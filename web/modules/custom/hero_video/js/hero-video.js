(function($) {
    $(document).ready(function() {
        var player;
        var paused = false;
        var sdkSrc = false;

        if ($('#hero-video-facebook').size() > 0) {
            sdkSrc = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.9";
            window.fbAsyncInit = function() {
                FB.init({
                    xfbml: true,
                    version: 'v2.9'
                });
                FB.Event.subscribe('xfbml.ready', function(msg) {
                    if (msg.id !== 'hero-video-facebook') {
                        return;
                    }
                    player = msg.instance;
                    player.playVideo = player.play; //standardizing syntax
                    player.subscribe('startedPlaying', videoStarted);
                    player.subscribe('finishedPlaying', videoFinished);
                    player.subscribe('paused', videoPaused);
                    playerReady();
                });
                //Firefox sometimes doesn't catch element in full-document "xfbml" scan,
                //so manually trigger it here
                var wrapper = document.getElementsByClassName('hero-video-container')[0];
                FB.XFBML.parse(wrapper);
            };
        } else if ($('#hero-video-youtube').size() > 0) {
            sdkSrc = "//www.youtube.com/iframe_api";
            window.onYouTubeIframeAPIReady = function () {
                player = new YT.Player('hero-video-youtube', {
                    playerVars: {
                        rel: 0,
                        modestbranding: 1
                    },
                    videoId: parseYoutubeVideoId($('#hero-video-youtube').data('href')),
                    events: {
                        'onStateChange': function(event) {
                            if (event.data === YT.PlayerState.PLAYING) {
                                videoStarted();
                            } else if (event.data === YT.PlayerState.ENDED) {
                                videoFinished();
                            } else if (event.data === YT.PlayerState.PAUSED) {
                                videoPaused();
                            }
                        }
                    }
                });
                playerReady();
            };
        }

        (function(d, s, id) {
            if (sdkSrc === false) { //No known hero video on page
                return;
            }
            if (d.getElementById(id)) return;
            var js = d.createElement(s);
            js.id = id;
            js.src = sdkSrc;
            window.setTimeout(function() {
                d.getElementsByTagName('body')[0].appendChild(js);
            }, 1);
        }(document, 'script', 'hero-video-sdk'));

        function playerReady() {
            $('.hero-image-container')
                .css('cursor', 'pointer')
                .click(startVideo);
            $('.hero-image-play-button').show();
        }
        function videoStarted() {
            paused = false;
            $('.hero-text-container').hide();
        }
        function videoFinished() {
            paused = false;
            var imageContainer = $('.hero-image-container');
            if (imageContainer.size() > 0) {
                $('.hero-video-container').hide();
                imageContainer.show();
            }
            $('.hero-text-container').show();
        }
        function videoPaused() {
            paused = true;
            setTimeout(function() {
                if (paused) {
                    $('.hero-text-container').fadeIn();
                }
            }, 1000);
        }

        function startVideo() {
            $('.hero-text-container').hide();
            $('.hero-image-container').hide();
            $('.hero-video-container').show();
            player.playVideo();
        }

        /**
         * Get the Youtube Video id.
         * Source: https://github.com/radiovisual/get-video-id
         */
        function parseYoutubeVideoId(str) {
            // shortcode
            var shortcode = /youtube:\/\/|https?:\/\/youtu\.be\//g;

            if (shortcode.test(str)) {
                var shortcodeid = str.split(shortcode)[1];
                return stripParameters(shortcodeid);
            }

            // /v/ or /vi/
            var inlinev = /\/v\/|\/vi\//g;

            if (inlinev.test(str)) {
                var inlineid = str.split(inlinev)[1];
                return stripParameters(inlineid);
            }

            // v= or vi=
            var parameterv = /v=|vi=/g;

            if (parameterv.test(str)) {
                var arr = str.split(parameterv);
                return arr[1].split('&')[0];
            }

            // embed
            var embedreg = /\/embed\//g;

            if (embedreg.test(str)) {
                var embedid = str.split(embedreg)[1];
                return stripParameters(embedid);
            }

            // user
            var userreg = /\/user\//g;

            if (userreg.test(str)) {
                var elements = str.split('/');
                return stripParameters(elements.pop());
            }

            // attribution_link
            var attrreg = /\/attribution_link\?.*v%3D([^%&]*)(%26|&|$)/;

            if (attrreg.test(str)) {
                return str.match(attrreg)[1];
            }
        }
        /**
         * Strip away any parameters following '?'
         */
        function stripParameters(str) {
            if (str.indexOf('?') > -1) {
                return str.split('?')[0];
            }
            return str;
        }
    });
})(jQuery);
