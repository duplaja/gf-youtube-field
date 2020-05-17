// Set the name of the hidden property and the change event for visibility
var hidden, visibilityChange; 
if (typeof document.hidden !== "undefined") { // Opera 12.10 and Firefox 18 and later support 
    hidden = "hidden";
    visibilityChange = "visibilitychange";
} else if (typeof document.msHidden !== "undefined") {
    hidden = "msHidden";
    visibilityChange = "msvisibilitychange";
} else if (typeof document.webkitHidden !== "undefined") {
    hidden = "webkitHidden";
    visibilityChange = "webkitvisibilitychange";
}

jQuery(document).on('gform_post_render',function() {
    var tag = document.createElement('script');
    tag.src = "https://youtube.com/iframe_api";
    var firstScriptTag = document.getElementsByTagName('script')[0];
    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

    var iframeids= [];
    var formids = [];
    jQuery( ".youtube-iframe").each(function(){
        iframeids.push(jQuery(this).attr('id'));
        var form = jQuery(this).closest('form');
        var formid = form.attr('id');
        if(!formids.includes(formid)) {
            formids.push(formid);
        
            form.submit(function( event ) {

                submittedFormId = event.currentTarget.id;

                iframeids.forEach(function(item, index){
                    
                    /*Makes sure that we only call pause and submit on the actual form we clicked the button on*/
                    if(formid == submittedFormId) {
                        var player = YT.get(item);
                        if(player.getPlayerState() == 1) {
                            event.preventDefault();
                            event.stopPropagation();
                            form.data('submit',1);
                            player.pauseVideo();    
                        }
                    }
                });                 
            });
        }
    });

});

function handleVisibilityChange() {
    if (document[hidden]) {
        jQuery( ".youtube-iframe").each(function(){
            var player = YT.get(jQuery(this).attr('id'));
            if(player.getPlayerState() == 1) {
                player.pauseVideo();  
            }
        });
    }
}
if (typeof document.addEventListener === "undefined" || hidden === undefined) {
    console.log("Auto-pause requires a browser, such as Google Chrome or Firefox, that supports the Page Visibility API.");
} else {
    // Handle page visibility change   
    document.addEventListener(visibilityChange, handleVisibilityChange, false);
}  

function onYouTubeIframeAPIReady() {

   jQuery('.youtube-iframe').each(function(){
      var player
      player = new YT.Player(jQuery(this).attr('id'), {
      events: {
        'onReady': onPlayerReady,
        'onStateChange': onPlayerStateChange,
        'onPlaybackRateChange': onPlaybackRateChange
       }
      });
    });
}

function onPlayerReady(event) {

    var containerid = '#'+event.target.getIframe().parentNode.id;
    var inputfield = containerid.replace("#video_container_","#");

    hashedfield = btoa(inputfield);

    outercontainer = containerid.replace('video','yt');

    if(jQuery(inputfield).val() == '100.00% watched - '+hashedfield || jQuery(inputfield).val() == '100.00% watched') {
        jQuery(outercontainer).css('background-color','lightgreen');
    }

    if (jQuery(inputfield).val() == '') {
        jQuery(inputfield).val('0.00% watched');
    }

    jQuery(containerid).data( 'playback', 0);
    jQuery(containerid).data( 'time', 0);
    jQuery(containerid).data( 'timestamp', 0);
    jQuery(containerid).data( 'finished', 0);
    jQuery(containerid).data( 'spenttime', 0);
    jQuery(containerid).data( 'watched', 0);
}

function onPlaybackRateChange(event) {

    var tempid = '#'+event.target.getIframe().parentNode.id;

    var messageid = 'message_'+event.target.getIframe().parentNode.id;


    var maxplayback = jQuery(tempid).data( 'maxplayback');   
    var newplayback = event.data;
    var oldplayback = jQuery(tempid).data( 'playback');
    var timestamp = jQuery(tempid).data( 'timestamp');

    if(event.target.getPlayerState() == 1) {
        if(timestamp != 0) {
            var d = new Date();
            var t = d.getTime();
            var start = jQuery(tempid).data( 'time');
            var newtime = start + ((t - timestamp)/1000)*oldplayback;
            jQuery(tempid).data( 'time', newtime);
            jQuery(tempid).data( 'timestamp', t );
        }
    }

    if(maxplayback < newplayback) {
        event.target.setPlaybackRate(maxplayback);
        document.getElementById(messageid).innerHTML = '<p class="youtube-error-message">The max playback speed for this video has been set to '+maxplayback+'x. Your playback speed has been adjusted to match.</p>';
    } 

    jQuery(tempid).data('playback',newplayback);

}
  
function onPlayerStateChange(event) {

    var d = new Date();
    var t = d.getTime();
    var tempid = '#'+event.target.getIframe().parentNode.id;
    var form = jQuery(tempid).closest('form');


    var currplayback = event.target.getPlaybackRate();

    if(jQuery(tempid).data( 'playback') == 0) {

        jQuery(tempid).data( 'playback',currplayback);
    }



        var containerid = event.target.getIframe().parentNode.parentNode.id;
        
        var timestamp = jQuery(tempid).data( 'timestamp');

        if(event.data == 1){
            if( timestamp == 0) {
                jQuery(tempid).data( 'timestamp', t );
            }
        }
        if(event.data != 1) {
            if(timestamp != 0) {

                var playbackspeed = jQuery(tempid).data( 'playback');
                var start = jQuery(tempid).data( 'time');
                var newtime = start + ((t - timestamp)/1000)*playbackspeed;

                jQuery(tempid).data( 'time', newtime);
                jQuery(tempid).data( 'timestamp', 0);
            }
        }
    
        if(event.data ==0) {
            jQuery(tempid).data( 'finished', 1);
        }


        checkninety = event.target.getDuration() * 0.95;

        var percentwatched = jQuery(tempid).data( 'time') * 100 / event.target.getDuration();

        if(percentwatched > 100) {
            percentwatched = 100;
        }

        var inputfield = tempid.replace("#video_container_","#");

        if (jQuery(tempid).data( 'time') > checkninety) {
            jQuery(tempid).data( 'spenttime', 1);       
        }

        if (jQuery(tempid).data( 'spenttime') == 1 && jQuery(tempid).data( 'spenttime') == 1 && jQuery(tempid).data( 'watched') == 0) {
            jQuery(tempid).data( 'watched', 1);
            jQuery('#'+containerid).css('background-color','lightgreen');
            if(jQuery(tempid).data('required')) {
                hashedfield = btoa(inputfield);
                percentwatched = 100;
                jQuery(inputfield).val(percentwatched.toFixed(2)+'% watched - '+hashedfield); 
            }

        } 
        if (!jQuery(tempid).data( 'required')) {
            jQuery(inputfield).val(percentwatched.toFixed(2)+'% watched');
        }
        if((event.data == 2 || event.data == 0) && form.data('submit')) {
            form.trigger('submit');
        }
}