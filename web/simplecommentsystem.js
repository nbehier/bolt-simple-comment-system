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
                emailAuthorSelector:     '#addcomment_author_email',
                nameAuthorSelector:      '#addcomment_author_display_name',
                bodySelector:            '#addcomment_body',
                avatarContainerSelector: '.bscsFieldAvatar',
                gravatarUrl:             'https://www.gravatar.com/avatar/XXX?s=40&d=mm',
                mentiongravatarUrl:      'https://www.gravatar.com/avatar/XXX?s=30&d=mm'
            };

        // The actual plugin constructor
        function Plugin ( element, options ) {
            this.form      = element;
            this.settings  = $.extend( {}, defaults, options );
            this._defaults = defaults;
            this._name     = pluginName;

            if ( typeof Bscs !== 'undefined' && Bscs.hasOwnProperty('mention') ) {
                this.settings.mention = Bscs.mention.datas;
            }

            this.init();
        }

        // Avoid Plugin.prototype conflicts
        $.extend( Plugin.prototype, {
            init: function() {
                this.secureForm();
                this.addFormEvents();
                this.displayAvatar();
                this.displayMention();
            },
            secureForm: function() {},
            addFormEvents: function() {
                var _this = this;

                $(this.settings.buttonSelector).on('click', function(event) {
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
                var gravatar = this.settings.gravatarUrl,
                     $avatar = $(this.form).find(this.settings.avatarContainerSelector),
                       email = $(this.settings.emailAuthorSelector).val();

                if ($avatar.length == 0) {
                    return;
                }

                if (email != '') {
                    email    = md5(email.trim().toLowerCase() );
                    gravatar = gravatar.replace('XXX', email);
                }

                $avatar.html('<img src="' + gravatar + '" alt="avatar"/>');
            },
            displayMention: function() {
                var $fields     = $('.js-bscs-mention').find('textarea'),
                    $avatar     = $(this.form).find(this.settings.avatarContainerSelector),
                    gravatar    = this.settings.mentiongravatarUrl,
                    tributeConf = {
                        trigger: '@',
                        menuItemTemplate: function (item) {
                            return item.original.value;
                        }
                    };

                if ( $fields.length == 0 ) {
                    return;
                }

                tributeConf.values = this.settings.mention;

                if ($avatar.length > 0) {
                    tributeConf.menuItemTemplate = function (item) {
                        var gravatar_url = gravatar.replace('XXX', item.original.avatar);
                        return '<img src="' + gravatar_url + '">' + item.original.value;
                    };
                }

                var tribute = new Tribute(tributeConf);
                tribute.attach($fields);
            },
            submit: function() {
                // @todo submit
                console.log('submit');
                $(this.form).submit();
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

    // Add Emoticons if necessary
    var $emoticonsContainer = $('.jsDisplayEmoticons');
    if ($emoticonsContainer.length > 0) {
        var config = $emoticonsContainer.data('emoticons-animate');
        $emoticonsContainer.find('.bscsCommentBody').emoticonize({
            'animate': config
        });
    }

    // Add dom markup for Mention
    if ( typeof Bscs !== 'undefined' && Bscs.hasOwnProperty('mention') ) {
        var $comments = $('.bscsCommentBody'),
            authors   = Bscs.mention.datas;

        var authorNames = $.map(authors, function(author, idx) {
            return '@' + author.value;
        });

        var regex = new RegExp(authorNames.join("|"),"gi");

        $comments.each(function(idx) {
            var $this = $(this),
                html  = $this.html();

            html = html.replace(regex, function(matched){
              return '<span class="bscs-tribute">' + matched + '</span>';
            });

            $this.html(html);
        })
    }
} );
