/*chordeditor.js*/
(function ( $ ) {

    $.fn.jTabChordEditor = function(opt) {
        var isValid = false;
        var jTabScheme = '';
        var jTabSchemeV2 = '';
        var schemeValues = {};
        var chordbox;

        //options
        var options = $.extend({
            scheme: "%X/X.X/X.X/X.X/X.X/X.X/X[???]"
        }, opt );

        //
        jTabScheme = options["scheme"];



        var setup = function(el) {
            if(!isValid) {
                el.html("Invalid chord jTabScheme: '" + jTabScheme + "' !");
                return;
            }
            var html = '<div class="chordbox">'
                + '<div class="jtab chordonly"></div>'
                + '</div>'
                + '<hr />'
                + '<input name="chordScheme-jtab" value="'+jTabScheme+'"/>'
                + '<input name="chordScheme-jtab-v2" value="'+jTabSchemeV2+'"/>'
                + '<input name="offset" data-slider-value="'+schemeValues["offset"]+'"/>'
                + '<input name="string1" data-slider-value="'+schemeValues["s1"]["fret"]+'"/>'
                + '';
            el.html(html);
            chordbox = $(".chordbox", el);


            //if you do this right away in a bootstrap modal - vertical positions will be messed up
            setTimeout(reRenderChord, 500);
        }



        var reRenderChord = function() {
            jtab.render($(".jtab", chordbox), jTabScheme);
        }

        /**
         * Translates original jTabScheme to array of values ready to be used for v2 notation
         * jTabScheme: %Fret/Finger.Fret/Finger.Fret/Finger.Fret/Finger.Fret/Finger.Fret/Finger[Name]
         * jTabSchemeV2: offset#finger@fret/finger@fret/finger@fret/finger@fret/finger@fret/finger@fret
         *
         */
        var elaborateOriginalJTabScheme = function() {
            var c = new jtabChord(jTabScheme);
            isValid = c.isValid;
            if (isValid) {
                schemeValues["offset"] = 0;
                schemeValues["s1"] = {};
                schemeValues["s2"] = {};
                schemeValues["s3"] = {};
                schemeValues["s4"] = {};
                schemeValues["s5"] = {};
                schemeValues["s6"] = {};
                console.log(JSON.stringify(c.chordArray));//[12,["0"],["13","2"],["14","1"],["14","X"],["14","X"],[-1]]
                var fret, finger;
                var lowFret = 999;
                var highFret = 0;
                $.each([1,2,3,4,5,6], function(i, si) {
                    var cp = c.chordArray[si];
                    //console.log("P"+si+": "+JSON.stringify(cp));
                    if(cp.length == 1) {
                        fret = (cp[0]==0?0:"x");
                        finger = false;
                    } else {
                        fret = parseInt(cp[0]);
                        finger = (cp[1]==0?false:cp[1]);
                        lowFret = (fret<lowFret?fret:lowFret);
                        highFret = (fret>highFret?fret:highFret);
                    }
                    schemeValues["s"+si]["finger"] = finger;
                    schemeValues["s"+si]["fret"] = fret;
                });
                console.log("schemeValues: "+JSON.stringify(schemeValues));

                ////elaborate low/high fret
                lowFret = (lowFret==999?0:lowFret);
                if(lowFret>0) {
                    $.each([1,2,3,4,5,6], function(i, si) {
                        if(parseInt(schemeValues["s"+si]["fret"])>0) {
                            schemeValues["s"+si]["fret"] = schemeValues["s"+si]["fret"] - lowFret + 1;
                        }
                    });
                }
                schemeValues["offset"] = lowFret;
                console.log("schemeValues: "+JSON.stringify(schemeValues));
                generateV2CordScheme();
            }
        }

        var generateV2CordScheme = function() {
            jTabSchemeV2 = schemeValues["offset"] + "#"
                + (schemeValues["s1"]["finger"]?schemeValues["s1"]["finger"]+"@":"")+schemeValues["s1"]["fret"] + "/"
                + (schemeValues["s2"]["finger"]?schemeValues["s2"]["finger"]+"@":"")+schemeValues["s2"]["fret"] + "/"
                + (schemeValues["s3"]["finger"]?schemeValues["s3"]["finger"]+"@":"")+schemeValues["s3"]["fret"] + "/"
                + (schemeValues["s4"]["finger"]?schemeValues["s4"]["finger"]+"@":"")+schemeValues["s4"]["fret"] + "/"
                + (schemeValues["s5"]["finger"]?schemeValues["s5"]["finger"]+"@":"")+schemeValues["s5"]["fret"] + "/"
                + (schemeValues["s6"]["finger"]?schemeValues["s6"]["finger"]+"@":"")+schemeValues["s6"]["fret"]
                + "";
        }


        //initialize the plugin on all elements and return
        return this.each(function() {
            elaborateOriginalJTabScheme();
            setup($(this));
        });
    };

}( jQuery ));