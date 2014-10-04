/* jTab.js */
;(function ($) {
    "use strict";

    var options = {
        debug: false,
        showCode: false,
    };

    $.fn.jTab = function (opt) {
        var isValid = false;
        var tabElement;
        var token = '';
        var parsedScheme = {};
        var chordbox;

        var log = function (msg) {
            if (options.debug) {
                console.log(msg);
            }
        };


        var render = function () {
            var raphaelContainer = $('<div></div>');
            tabElement.html(raphaelContainer);
            var canvas = Raphael(raphaelContainer.get(0), Raphael.fn.total_width, Raphael.fn.total_height);
            canvas.drawFretboard(parsedScheme);
            canvas.drawStringNotes(parsedScheme);
            //string notation code
            if(options.showCode) {
                $('<code />').text(token).appendTo(tabElement);
            }
        };


        /**
         *
         * @param el - a jQuery element
         */
        var setup = function(el) {
            tabElement = el;
            token = (tabElement.attr("data-jtab-scheme") || tabElement.text() || '').toUpperCase();
            if (parsedScheme = parseSchemeString(token)) {
                isValid = true;
            }
            tabElement.attr("data-jtab-valid", isValid);
            if (!isValid) {
                log("Invalid chord scheme: '" + token + "' !");
                return;
            }
            render();
        };



        /**
         * Parses the string rappresentation of the chord and converts it into usable finger/fret values
         * format: 0#X|3@3|2@2|0|1@1|0 - all letters are in uppercase X or T
         * @param {string} token
         * @return object|boolean
         */
        var parseSchemeString = function(parseToken) {
            token = parseToken.toUpperCase();
            log("parsing scheme string: " + token);
            var parsedScheme = {};
            parsedScheme.offset = parseInt(token.match(/^[0-9]*/)[0], 10);
            var strings = token.replace(/^[0-9]*#/g, '');
            var pairs = strings.split('|');
            if (pairs.length !== 6) { return false; }
            log("strings: " + JSON.stringify(pairs));
            //
            var i, pair, finger, fret;
            var valid = true;
            for (i = 0; i < 6; i++) {
                if (pairs[i].indexOf("@") !== -1) {
                    pair = pairs[i].split('@');
                    finger = (pair[0] === "T" ? pair[0] : parseInt(pair[0], 10));//T is for Thumb
                    fret = parseInt(pair[1], 10);
                } else {
                    finger = false;
                    fret = (pairs[i] === "X" ? pairs[i] : parseInt(pairs[i], 10));//X is mute string
                }
                //
                if (finger === "T" || (finger >= 1 && finger < 5)) {
                    if (fret < 1 || fret > 24) {
                        valid = false;
                        break;
                    }
                } else if (finger === false) {
                    if (fret !== 0 && fret !== "X") {
                        valid = false;
                        break;
                    }
                } else {
                    valid = false;
                    break;
                }
                parsedScheme["s" + (i + 1)] = {};
                parsedScheme["s" + (i + 1)].finger = finger;
                parsedScheme["s" + (i + 1)].fret = fret;
            }
            if (!valid) { return false; }
            return(parsedScheme);
        };


        var init = function(opt) {
            //extend options with passed opt argument
            options = $.extend(options, opt);

            //initialize the plugin on all elements and return
            return this.each(function(i) {
                setup($(this));
            });
        };

        var refresh = function(refreshToken) {
            if ((parsedScheme = parseSchemeString(refreshToken)) !== false) {
                isValid = true;
                tabElement = $(this);
                tabElement.attr("data-jtab-scheme", token);
                tabElement.attr("data-jtab-valid", isValid);
                render();
            }
            return(isValid);
        };

        var publicMethods = {
            refresh: refresh
        };


        /**
         * decide what to do with opt argument
         * {string} - call a public method by that name
         * {object}|null - init the plugin as normal
         */
        if ( publicMethods[opt] ) {
            return publicMethods[opt].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if (typeof opt === 'object' || !opt ) {
            return init.apply( this, arguments );
        } else {
            $.error('plugin[jTab]:There is no publicly available method by this name: ' +  opt);
        }
    };


    /**
     * Raphael SVG chord drawing methods
     */
    Raphael.fn.scale = 1;//todo: this could be a good idea
    Raphael.fn.margin_top = 12;
    Raphael.fn.margin_bottom = 2;
    Raphael.fn.margin_left = 8;
    Raphael.fn.margin_right = 24;

    Raphael.fn.current_offset = Raphael.fn.margin_left;

    Raphael.fn.string_spacing = 16;
    Raphael.fn.strings_drawn = 6;
    Raphael.fn.fret_spacing = 16;
    Raphael.fn.frets_drawn = 4;
    Raphael.fn.note_radius = 7;

    Raphael.fn.fret_width = Raphael.fn.string_spacing * ( Raphael.fn.strings_drawn);
    Raphael.fn.fret_height = Raphael.fn.fret_spacing * (Raphael.fn.frets_drawn + 0.5);

    Raphael.fn.total_height = Raphael.fn.margin_top + Raphael.fn.fret_height + Raphael.fn.margin_bottom;
    Raphael.fn.total_width = Raphael.fn.margin_left + Raphael.fn.fret_width + Raphael.fn.margin_right;

    Raphael.fn.color = "#000";
    Raphael.fn.tab_text_color = "#000";
    Raphael.fn.fingering_text_color = "#fff";


    Raphael.fn.svg_params = function(x,y,l1,l2) {
        // http://www.w3.org/TR/SVG/paths.html#PathData --helpful reading
        var move_line_to = "m"+x+" "+y+"l"+l1+" "+l2
        if(arguments.length == 4) return move_line_to
    }


    Raphael.fn.drawFretboard = function(schemeValues) {
        var fret_left = this.current_offset + this.margin_left;
        var fret_labels = [ '', '', '', 'III', '', 'V', '', 'VII', '', 'IX', '', '', 'XII', '', '', 'XV', '', 'XVII', '', 'XIX', '', 'XXI', '' ];

        var stroke_width = (schemeValues.offset == 0 ? 3 : 0);  // nut
        var chord_fretboard_path = this.path(this.svg_params(fret_left,this.margin_top,this.string_spacing * (this.strings_drawn - 1),0))
        chord_fretboard_path.attr({stroke: this.color, "stroke-width":stroke_width })


        for (var i = 0; i <= this.frets_drawn; i++ ) { // frets
            this.path(this.svg_params(fret_left,this.margin_top + (i * this.fret_spacing),this.string_spacing * (this.strings_drawn - 1), 0));
            var fretLabelIndex = schemeValues.offset+i;
            var pos = ( fret_labels[fretLabelIndex] === undefined ) ? '' : fret_labels[fretLabelIndex];
            if ( pos.length > 0 && i > 0) { // draw fret position
                var posX = this.fret_width + this.string_spacing + (this.note_radius/2);
                this.text(
                    posX,
                    this.margin_top + ( ( i - 0.5 ) * this.fret_spacing),
                    pos).attr({stroke: this.tab_text_color, "font-size":"11px"});
            }
        }
        for (var i = 0; i < this.strings_drawn; i++ ) {
            this.path(this.svg_params(fret_left + (i * this.string_spacing),this.margin_top,0, this.fret_spacing * (this.frets_drawn + 0.5)))  // strings
        }
    }

    // draw notes on chords
    Raphael.fn.drawStringNotes = function (schemeValues) {
        var noteObject, fingerLetter, fret_dy;
        var fret_left = this.current_offset + this.margin_left;
        for (var i = 1; i <= 6 ; i++) {
            noteObject = schemeValues["s"+i];
            switch(noteObject.fret) {
                case "X": //muted/not played
                case 0: //open string
                    fingerLetter = (noteObject.fret==="X"?"x":"o");
                    this.text(fret_left + (i - 1) * this.string_spacing, this.margin_top - 8, fingerLetter).attr({stroke: this.tab_text_color, "font-size":"9px"});
                    break;
                default:
                    fret_dy = (noteObject.fret - 0.5) * this.fret_spacing;
                    this.circle(
                        fret_left + (i - 1) * this.string_spacing,
                        this.margin_top + fret_dy, this.note_radius).attr({stroke: this.color, fill: this.color});
                    if (noteObject.finger !== false) {
                        this.text( fret_left + (i - 1) * this.string_spacing,
                            this.margin_top + fret_dy, noteObject.finger ).attr({fill: this.fingering_text_color, "font-size":"12px"});
                    }
                    break;
            }
        }
    }


}(jQuery));
