/*
 *  jquery-boilerplate - v4.1.0
 *  A jump-start for jQuery plugins development.
 *  http://jqueryboilerplate.com
 *
 *  Made by Zeno Rocha
 *  Under MIT License
 */
;( function( $, window, document, undefined ) {

    "use strict";

        // Create the defaults once
        var pluginName = "jqSimpleCommentsSystem",
            defaults   = {
                honeypotSelector:        '#bscsDetect',
                buttonSelector:          '#addcomment_post',
                emailAuthorSelector:     '#addcomment_email',
                nameAuthorSelector:      '#addcomment_name',
                bodySelector:            '#addcomment_body',
                avatarContainerSelector: '.bscsFieldAvatar'
            };

        // The actual plugin constructor
        function Plugin ( element, options ) {
            this.form      = element;
            this.settings  = $.extend( {}, defaults, options );
            this._defaults = defaults;
            this._name     = pluginName;

            this.init();
        }

        // Avoid Plugin.prototype conflicts
        $.extend( Plugin.prototype, {
            init: function() {
                this.secureForm();
                this.addFormEvents();
                this.displayAvatar();
            },
            secureForm: function() {},
            addFormEvents: function() {
                var _this = this;

                $(this.form).on('submit', function(event) {
                    event.preventDefault();

                    if ( _this.hasDetectSpam() ) {
                        console.log("spam detect !");
                        return;
                    }

                    if ( _this.isValidAuthor() ) {
                        _this.submit();
                    }
                    else {
                        _this.setDraft();
                    }
                });

                $(this.settings.emailAuthorSelector).on('change', function() {
                    _this.displayAvatar();
                });
            },
            hasDetectSpam: function() {
                return ( $(this.settings.honeypotSelector).val() != '' );
            },
            isValidAuthor: function() {
                // @todo check email
                return ( $(this.settings.emailAuthorSelector).val() != '' && $(this.settings.nameAuthorSelector).val() != '' );
            },
            setDraft: function() {
                console.log("Not valid author !");
            },
            displayAvatar: function() {
                var gravatar = "https://www.gravatar.com/avatar/XXX?s=40&d=mm",
                       email = $(this.settings.emailAuthorSelector).val();

                if (email != '') {
                    email    = md5(email.trim().toLowerCase() );
                    gravatar = gravatar.replace('XXX', email);
                }

                $(this.settings.avatarContainerSelector).html('<img src="' + gravatar + '" alt="avatar"/>');
            },
            submit: function() {
                // @todo submit
                console.log('submit');
            }
        } );

        // A really lightweight plugin wrapper around the constructor,
        // preventing against multiple instantiations
        $.fn[ pluginName ] = function( options ) {
            return this.each( function() {
                if ( !$.data( this, "plugin_" + pluginName ) ) {
                    $.data( this, "plugin_" +
                        pluginName, new Plugin( this, options ) );
                }
            } );
        };

} )( jQuery, window, document );

$( function() {
    $( "#bscsForm" ).jqSimpleCommentsSystem({});
} );
