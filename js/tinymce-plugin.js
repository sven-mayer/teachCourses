/**
 * This file contains js functions for the teachcorses tinyMCE plugin.
 * 
 * @package teachcorses
 * @subpackage js
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

(function() {
    /**
     * A JavaScript equivalent of PHP’s stripslashes
     * Source: http://phpjs.org/functions/stripslashes/
     * @param {string} str
     * @returns {string} 
     * @since 5.0.0
     */
    function tc_stripslashes(str) {
        return (str + '')
          .replace(/\\(.?)/g, function(s, n1) {
            switch (n1) {
              case '\\':
                return '\\';
              case '0':
                return '\u0000';
              case '':
                return '';
              default:
                return n1;
            }
          });
    }
    
    /**
     * Gets a cookie
     * @param {string} cname    The name of the cookie
     * @returns {string}        The value of the cookie
     * @since 5.0.0
     */
    function tc_getCookie(cname) {
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for(var i=0; i<ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0)===' ') c = c.substring(1);
            if (c.indexOf(name) !== -1) return c.substring(name.length, c.length);
        }
        return "";
    }
    
    /**
     * Sets a cookie
     * @param {string} cname            The name of the cookie
     * @param {string} cvalue           The value of the cookie
     * @param {int} exdays              The number of days, where the cookie will be expire
     * @since 5.0.0
     */
    function tc_setCookie(cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays*24*60*60*1000));
        var expires = "expires="+d.toUTCString();
        document.cookie = cname + "=" + cvalue + "; " + expires + "; path=" + teachcorses_cookie_path;
    }
    
    /**
     * teachCorses tinyMCE Plugin
     * @since 5.0.0
     */
    tinymce.PluginManager.add('teachcorses_tinymce', function( editor, url ) {
        editor.addButton( 'teachcorses_tinymce', {
            text: 'teachCorses',
            icon: false,
            type: 'menubutton',
            menu: [
                {
                    text: 'Add document',
                    onclick: function() {
                        
                        editor.windowManager.open( {
                            url: teachcorses_editor_url,
                            title: 'teachCorses Document Manager',
                            id: 'tc_document_manager',
                            inline: 1,
                            width: 950,
                            height: 560,
                            buttons: [
                            
                            {
                                text: 'Insert',
                                onclick: function(){
                                    
                                    // read cookie
                                    var data_store = tc_getCookie("teachcorses_data_store");
                                    
                                    // build insert string
                                    // alert(data_store);
                                    var insert = '';
                                    var data = data_store.split(":::");
                                    var length = data.length;
                                    for ( var i = 0; i < length; i++ ) {
                                        if ( data[i] === "") {
                                            continue;
                                        }
                                        data[i] = data[i].replace('[','');
                                        data[i] = data[i].replace(']','');
                                        // console.log(data[i]);
                                        var data_single = data[i].split(",");
                                        var file_name = '', file_url = '';
                                        for ( var j = 0; j < 2; j++ ) {
                                            var data_inline = data_single[j].split(" = ");
                                            data_inline[1] = data_inline[1].replace('{"','');
                                            data_inline[1] = data_inline[1].replace('"}','');
                                            if ( j === 0 ) {
                                                file_name = data_inline[1];
                                            }
                                            if ( j === 1 ) {
                                                file_url = data_inline[1];
                                            }
                                            // console.log(data_inline[1]);
                                            
                                        }
                                        insert = insert + '<a class="' + teachcorses_file_link_css_class + '" href="' + file_url + '">' + tc_stripslashes(file_name) + '</a> ';
                                        // console.log(insert);
                                    }
                                    
                                    // insert into editor
                                    editor.insertContent(insert);
                                    editor.windowManager.close();
                                    
                                    // reset cookie
                                    tc_setCookie("teachcorses_data_store", "", 1);
                                }
                            },
                            {
                                text: 'Close',
                                onclick: function () {
                                    editor.windowManager.close();
                                    tc_setCookie("teachcorses_data_store", "", 1);
                                }
                            }
                                
                        ]
                        });
                    }
                },
                {
                    text: 'Insert shortcode (courses)',
                    menu: [
                        
                        // [tpcourselist]
                        
                        {
                            text: 'List of courses [tpcourselist]',
                            onclick: function() {
                                editor.windowManager.open( {
                                    title: 'Insert a list of courses [tpcourselist]',
                                    body: [
                                        {
                                            type: 'listbox',
                                            name: 'tc_image',
                                            label: 'Show images',
                                            'values': [
                                                {text: 'none', value: 'none'},
                                                {text: 'left', value: 'left'},
                                                {text: 'right', value: 'right'},
                                                {text: 'bottom', value: 'bottom'}
                                            ]
                                        },
                                        {
                                            type: 'textbox',
                                            name: 'tc_size',
                                            label: 'Image size in px',
                                            value: '0'
                                        },
                                        {
                                            type: 'listbox',
                                            name: 'tc_headline',
                                            label: 'Show headline',
                                            'values': [
                                                {text: 'show', value: '1'},
                                                {text: 'hide', value: '0'}
                                            ]
                                        },
                                        {
                                            type: 'textbox',
                                            name: 'tc_text',
                                            label: 'Custom text under the headline',
                                            value: '',
                                            multiline: true,
                                            minWidth: 300,
                                            minHeight: 100
                                        },
                                        {
                                            type: 'listbox',
                                            name: 'tc_term',
                                            label: 'Term',
                                            'values': teachcorses_semester // is written with tc_write_data_for_tinymce()
                                        }
                                    ],
                                    onsubmit: function( e ) {
                                        var image = e.data.tc_image;
                                        var image_size = e.data.tc_size;
                                        var headline = e.data.tc_headline;
                                        var text = e.data.tc_text;
                                        var term = e.data.tc_term;
                                        
                                        image = (image === 'none') ? '' : 'image="' + image + '"';
                                        image_size = (image_size === '0') ? '' : 'image_size="' + image_size + '"';
                                        headline = (headline === '1') ? '' : 'headline="' + headline + '"';
                                        text = (text === '') ? '' : 'text="' + text + '"';
                                        term = (term === '') ? '' : 'term="' + term + '"';
                                        
                                        editor.insertContent( '[tpcourselist ' + image + ' ' + image_size + ' ' + headline + ' ' + text + ' ' + term + ']');
                                    }
                                });
                            }
                        },
                        
                        // [tpcoursedocs]
                        
                        {
                            text: 'Course documents [tpcoursedocs]',
                            onclick: function() {
                                editor.windowManager.open( {
                                    title: 'Insert a list of course documents [tpcoursedocs]',
                                    body: [
                                        {
                                            type: 'listbox',
                                            name: 'tc_coure_id',
                                            label: 'Select course',
                                            minWidth: 570,
                                            'values': teachcorses_courses //  is written by tc_write_data_for_tinymce()
                                        },
                                        {
                                            type: 'textbox',
                                            name: 'tc_link_class',
                                            label: 'CSS class for links',
                                            value: teachcorses_file_link_css_class // is written by tc_write_data_for_tinymce()
                                        },
                                        {
                                            type: 'textbox',
                                            name: 'tc_date_format',
                                            label: 'Date format',
                                            value: 'd.m.Y'
                                        },
                                        {
                                            type: 'listbox',
                                            name: 'tc_show_date',
                                            label: 'Show upload date for documents',
                                            'values': [
                                                {text: 'Yes', value: '1'},
                                                {text: 'No', value: '0'}
                                            ]
                                        },
                                        {
                                            type: 'listbox',
                                            name: 'tc_numbered',
                                            label: 'Use a numbered list',
                                            'values': [
                                                {text: 'Yes', value: '1'},
                                                {text: 'No', value: '0'}
                                            ]
                                        },
                                        {
                                            type: 'listbox',
                                            name: 'tc_headline',
                                            label: 'Show headline',
                                            'values': [
                                                {text: 'Yes', value: '1'},
                                                {text: 'No', value: '0'}
                                            ]
                                        }
                                    ],
                                    onsubmit: function( e ) {
                                        var id = e.data.tc_coure_id;
                                        var link_class = e.data.tc_link_class;
                                        var date_format = e.data.tc_date_format;
                                        var show_date = e.data.tc_show_date;
                                        var numbered = e.data.tc_numbered;
                                        var headline = e.data.tc_headline;
                                        
                                        id = (id === '0') ? '' : 'id="' + id + '"';
                                        link_class = (link_class === teachcorses_file_link_css_class) ? '' : 'link_class="' + link_class + '"';
                                        date_format = (date_format === 'd.m.Y') ? '' : 'date_format="' + date_format + '"';
                                        show_date = (show_date === '1') ? '' : 'show_date="' + show_date + '"';
                                        numbered = (numbered === '1') ? '' : 'numbered="' + numbered + '"';
                                        headline = (headline === '1') ? '' : 'headline="' + headline + '"';
                                        
                                        editor.insertContent( '[tpcoursedocs ' + id + ' ' + link_class + ' ' + date_format + ' ' + show_date + ' ' + numbered + ' ' + headline + ']');
                                    }
                                });
                            }
                        },
                        
                        // [tpcourseinfo]
                        
                        {
                            text: 'Course information [tpcourseinfo]',
                            onclick: function() {                     
                                editor.windowManager.open( {
                                    title: 'Insert course information [tpcourseinfo]',
                                    body: [
                                        {
                                            type: 'listbox',
                                            name: 'tc_coure_id',
                                            label: 'Select course',
                                            minWidth: 570,
                                            'values': teachcorses_courses // is written by tc_write_data_for_tinymce()
                                        },
                                        {
                                            type: 'listbox',
                                            name: 'tc_show_meta',
                                            label: 'Show meta data',
                                            'values': [
                                                {text: 'Yes', value: '1'},
                                                {text: 'No', value: '0'}
                                            ]
                                        }
                                    ],
                                    onsubmit: function( e ) {
                                        var id = e.data.tc_coure_id;
                                        var show_meta = e.data.tc_show_meta;
                                        
                                        id = (id === '0') ? '' : 'id="' + id + '"';
                                        show_meta = (show_meta === '1') ? '' : 'show_meta="' + show_meta + '"';
                                        
                                        editor.insertContent( '[tpcourseinfo ' + id + ' ' + show_meta + ']');
                                    }
                                });
                            }
                        }
                    ]
                },
            ]
        });
    });
})();