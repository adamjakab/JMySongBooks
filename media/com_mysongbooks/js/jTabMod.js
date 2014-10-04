/**
 * JTabMod - Javascript/CSS Guitar Chord Notation and Editor for the Web.
 * This entire library was taken from the excellent wotk of the original jTab library
 * and modified to the specific need of this component by Adam Jakab(http://devshed.jakabadambalazs.com)
 *
 * ----------------------------------------------------------------------------------------
 * ------------------------------------ORIGINAL jTab DISCLAIMER-----------------------------
 * Version 1.3.1
 * Written by Paul Gallagher (http://tardate.com), 2009. (original version and maintainer)
 * Contributions:
 *   Jason Ong (https://github.com/jasonong)
 *   Bruno Bornsztein (https://github.com/bborn)
 *   Binary Bit LAN (https://github.com/binarybitlan)
 * See:
 *   http://jtab.tardate.com : more information on availability, configuration and use.
 *   http://github.com/tardate/jtab/tree/master : source code repository, wiki, documentation
 *
 * This library also depends on the following two libraries that must be loaded for it to work:
 *   jQuery - http://www.jquery.com/
 *   Raphael - http://raphaeljs.com/
 *
 *
 * This library is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General
 * Public License as published by the Free Software Foundation; either version 2.1 of the License, or (at your option)
 * any later version.
 *
 * This library is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Lesser General Public License along with this library; if not, write to
 * the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 */

//
// define extensions to the Raphael class
//

Raphael.fn.tabtype = 0;  // 0 = none, 1 = tab & chord, 2 = chord, 3 = tab
Raphael.fn.has_chord = false;
Raphael.fn.has_tab = false;

Raphael.fn.debug = false;
Raphael.fn.scale = 1;
Raphael.fn.margin_top = 12;
Raphael.fn.margin_bottom = 2;
Raphael.fn.margin_left = 8;
Raphael.fn.margin_right = 4;

Raphael.fn.current_offset = Raphael.fn.margin_left;

Raphael.fn.string_spacing = 16;
Raphael.fn.strings_drawn = 6;
Raphael.fn.fret_spacing = 16;
Raphael.fn.frets_drawn = 4;
Raphael.fn.note_radius = 7;

Raphael.fn.fret_width = Raphael.fn.string_spacing * ( Raphael.fn.strings_drawn - 1 );
Raphael.fn.fret_height = Raphael.fn.fret_spacing * (Raphael.fn.frets_drawn + 0.5);
Raphael.fn.chord_width = Raphael.fn.margin_left + Raphael.fn.fret_width + Raphael.fn.string_spacing + Raphael.fn.margin_right;
Raphael.fn.chord_height = Raphael.fn.margin_top + Raphael.fn.fret_height + Raphael.fn.margin_bottom;

Raphael.fn.tab_current_string = 0; // 1,2,3,4,5,6 or 0 = not set
Raphael.fn.tab_margin_top = 10;
Raphael.fn.tab_top = Raphael.fn.chord_height + Raphael.fn.tab_margin_top;
Raphael.fn.tab_spacing = Raphael.fn.fret_spacing;
Raphael.fn.tab_height = Raphael.fn.tab_spacing * 5;
Raphael.fn.tab_char_width = 8;

Raphael.fn.total_height = Raphael.fn.tab_top + Raphael.fn.tab_height + Raphael.fn.margin_bottom;

Raphael.fn.color = "#000";
Raphael.fn.fingering_text_color = "#fff";
Raphael.fn.tab_text_color = "#000";


// main drawing routine entry point: to render a token - chord or tab
Raphael.fn.render_token = function (token) {
    var c = new jtabChord(token);
    if (c.isValid) { // draw chord
        var chordObject = c.chordObject;
        //console.log("Got chordObject: " + JSON.stringify(chordObject));
        this.chord_fretboard(chordObject.offset);
        //
        for (var i = 1; i <= 6 ; i++) {
            this.chord_note(chordObject.offset, i, chordObject["s"+i]);
        }
        //
        //this.increment_offset();
    }
    return(c.isValid);
}


// draw a note in a chord
Raphael.fn.chord_note = function (offset, string_number, noteObject) {
    //var fret_number = noteObject[0];
    var fret_left = this.current_offset + this.margin_left;

    if (noteObject["fret"] == "x") {// x -> muted/not played
        this.text(fret_left + (string_number - 1) * this.string_spacing, this.margin_top - 8, "x").attr({stroke: this.tab_text_color, "font-size":"9px"});
    } else if (noteObject["fret"] == 0) {// 0 -> open string
        this.text(fret_left + (string_number - 1) * this.string_spacing, this.margin_top - 8, "o").attr({stroke: this.tab_text_color, "font-size":"9px"});
    } else {
        var fret_dy = (noteObject["fret"] - 0 - 0.5) * this.fret_spacing;
        //var circle =
        this.circle(
            fret_left + (string_number - 1) * this.string_spacing,
            this.margin_top + fret_dy, this.note_radius).attr({stroke: this.color, fill: this.color});
        if (noteObject["finger"] !== false) {
            this.text( fret_left + (string_number - 1) * this.string_spacing,
                this.margin_top + fret_dy, noteObject["finger"] ).attr({fill: this.fingering_text_color, "font-size":"12px"});
        }
    }

    /*if ( this.has_tab && fret_number >= 0 ) {
        this.draw_tab_note( (this.strings_drawn - string_number + 1), fret_number, this.margin_left + this.string_spacing * 2.5 );
    }*/
}


//draw the fretboard
Raphael.fn.chord_fretboard = function ( offset ) {
    var fret_left = this.current_offset + this.margin_left;
    var fret_labels = [ '', '', '', 'III', '', 'V', '', 'VII', '', 'IX', '', '', 'XII', '', '', 'XV', '', 'XVII', '', 'XIX', '', 'XXI', '' ];

    var stroke_width = offset == 0 ? 3 : 0  // nut
    var chord_fretboard_path = this.path(this.svg_params(fret_left,this.margin_top,this.string_spacing * (this.strings_drawn - 1),0))
    chord_fretboard_path.attr({stroke: this.color, "stroke-width":stroke_width })

    for (var i = 0; i <= this.frets_drawn; i++ ) { // frets
        this.path(this.svg_params(fret_left,this.margin_top + (i * this.fret_spacing),this.string_spacing * (this.strings_drawn - 1), 0));
        var fretLabelIndex = (offset==0?offset+i:offset+i);
        pos = ( fret_labels[fretLabelIndex] === undefined ) ? '' : fret_labels[fretLabelIndex];
        if ( pos.length > 0 ) { // draw fret position
            this.text(
                fret_left + this.fret_width + this.string_spacing * 1.0,
                this.margin_top + ( ( i - 0.5 ) * this.fret_spacing),
                pos).attr({stroke: this.tab_text_color, "font-size":"12px"});
        }
    }
    for (var i = 0; i < this.strings_drawn; i++ ) {
        this.path(this.svg_params(fret_left + (i * this.string_spacing),this.margin_top,0, this.fret_spacing * (this.frets_drawn + 0.5)))  // strings
    }
}

Raphael.fn.svg_params = function(x,y,l1,l2) {
    // http://www.w3.org/TR/SVG/paths.html#PathData --helpful reading
    var move_line_to = "m"+x+" "+y+"l"+l1+" "+l2
    if(arguments.length == 4) return move_line_to
}







function jtabChord (token) {
    this.chordObject = null;
    this.isValid = false;
    this.chordName = '?';
    this.fullChordName = token.toLowerCase();
    var parts = this.fullChordName.match( /\[(.+?)\]/ );
    this.chordName = (parts?parts[1]:"?");
    this.chordObject = this.parseFromToken();
}

jtabChord.prototype.parseFromToken = function() {
    var co = {};
    //console.log("parsing token: " + this.fullChordName);//0#x/3@3/2@2/0/1@1/0
    co["token"] = this.fullChordName;
    co["offset"] = parseInt(this.fullChordName.match(/^[0-9]*/)[0]);
    //
    var notes = this.fullChordName.replace(/^[0-9]*#/g, '');
    pairs = notes.split('/');
    if (pairs.length != 6){return;}
    //
    var finger, fret;
    var valid = true;
    for (var i = 0; i < 6; i++){
        if(pairs[i].indexOf("@") != -1) {
            pair = pairs[i].split('@');
            finger = (pair[0]=="t"?pair[0]:parseInt(pair[0]));//t is for Thumb
            fret = parseInt(pair[1]);
        } else {
            finger = false;
            fret = (pairs[i]=="x"?pairs[i]:parseInt(pairs[i]));
        }
        //
        if (finger=="t" || (finger>=1 && finger<5)) {
            if(fret<1 || fret>24) {
                valid = false; break;
            }
        } else if (finger===false) {
            if(fret!="0" && fret != "x") {
                valid = false; break;
            }
        } else {
            valid = false; break;
        }
        co["s"+(i+1)] = {};
        co["s"+(i+1)]["finger"] = finger;
        co["s"+(i+1)]["fret"] = fret;
    }
    if (!valid) {return;}
    this.isValid = true;
    return co;
};





var jTabMod = {
    element_count:0,
    version: '1.3.1'
};



// set color pallette for the jtab rendering
jTabMod.setPalette = function (element) {
    var fgColor = '#000';
    Raphael.fn.color = fgColor;
    Raphael.fn.tab_text_color = fgColor;
    var bgColor = '#fff';
    Raphael.fn.fingering_text_color = bgColor;
}


// Render the tab for a given +element+.
// +element+ is a DOM node
// +notation_text+ is the optional notation to render (if not specified, +element+ text content will be used)
// After rendering, the +element+ will be given the additional "rendered" class.
jTabMod.render = function (element, notation_text) {
    var notation = notation_text || jQuery(element).text() || '';
    var tabtype = 1;
    var rndID="builder_" + jTabMod.element_count++;

    // add the Raphael canvas in its own DIV. this gets around an IE6 issue with not removing previous renderings
    var canvas_holder = jQuery('<div id="'+rndID+'"></div>').css({height: Raphael.fn.total_height});

    jQuery(element).html(canvas_holder);
    jTabMod.setPalette(element);
    canvas = Raphael(rndID, 128, 86);
    var res = canvas.render_token(notation);
    if(res) {
        jQuery(element).addClass('rendered');
    }
    return(res);
}


// Render all nodes with class 'jtab'.
// +within_scope+ is an optional selector that will restrict rendering to only those nodes contained within.
jTabMod.renderimplicit = function(within_scope) {
    jQuery('.jtab', within_scope).not('.rendered').each( function(name, index) { jTabMod.render(this); } );
}


// initialize jTabMod library - Sets up to run implicit rendering on window.onload
jTabMod.init = function() {
    var oldonload = window.onload;
    window.onload = function() {
        if (typeof oldonload == 'function') oldonload();
        jTabMod.renderimplicit(null);
    }
}


// bootstrap jTabMod when jQuery is ready
jQuery(document).ready(function($) {
    jTabMod.init();
});
