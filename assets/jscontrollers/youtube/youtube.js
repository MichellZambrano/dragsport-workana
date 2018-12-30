
var API_KEY = 'AIzaSyDrobI9xFqwF6Of3yvjpMdePk6HZm4hejk';
var CHANNEL_ID = 'UC0SxrdrKBWyqZTYCy93px7g';
var loading = true;


$.get('https://www.googleapis.com/youtube/v3/channels', {
    part: 'contentDetails',
    id: CHANNEL_ID,
    key: API_KEY
}, function(data){
    $.each(data.items, function(i,e){
        var plalistId = e.contentDetails.relatedPlaylists.uploads;
        getVideos(plalistId);
    });
});


function getVideos(plalistId){
    $.ajax({
        type: 'GET',
        dataType: 'json',
        url: 'https://www.googleapis.com/youtube/v3/playlistItems',
        data:  {
            part : 'snippet', 
            maxResults : 20,
            playlistId : plalistId,
            key: API_KEY
        },
        beforeSend: function () {},
        success: function(data){
            loading = false;

            if (data.items.length > 0) {
                $.each(data.items, function(i, e){
                    $('#youtube-container').append(`<div class="col-md-6 col-xl-4">
                        <div class="post-video">
                            <div class="video-thumb">
                                <img src="${e.snippet.thumbnails.high.url}" alt="photo">
                                <a href="https://youtube.com/watch?v=${e.snippet.resourceId.videoId}" class="play-video">
                                    <svg class="olymp-play-icon"><use xlink:href="assets/dragsport/svg-icons/sprites/icons.svg#olymp-play-icon"></use></svg>
                                </a>
                            </div>
                    
                            <div class="video-content">
                                <a href="https://youtube.com/watch?v=${e.snippet.resourceId.videoId}" title="${e.snippet.title}" class="h4 title video-play">${e.snippet.title.length > 48 ? e.snippet.title.substr(0,50) + '...' : e.snippet.title }</a>
                                <p>${e.snippet.description}</p>
                                <a href="https://www.youtube.com/channel/${CHANNEL_ID}" target="_blank" class="link-site">YOUTUBE.COM</a>
                            </div>
                        </div>
                    </div>`);
                });
            }
            else{
                $('#youtube-content').html(`<div class="youtube-end-content">
                        <div class="title d-flex align-items-center align-content-center justify-content-center">
                            <i class="fab fa-youtube icon"></i>
                            <span>YouTube</span>
                        </div>
                        <div class="youtube-loader d-flex align-items-center align-content-center justify-content-center">
                            <div class="loading-videos-channel">There are still no videos available.</div>
                        </div>
                    </div>`);
            }   

            CRUMINA.mediaPopups();
        },
        error: function (xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
            console.log(thrownError);
        },
        progress: function(e) {
            //make sure we can compute the length
            if(e.lengthComputable) {
                //calculate the percentage loaded
                var pct = (e.loaded / e.total) * 100;

                //log percentage loaded
                console.log(pct);
            }
            //this usually happens when Content-Length isn't set
            else {
                console.warn('Content Length not reported!');
            }
        },
        complete: function () {
            if(!loading){
                $('.youtube-loading-content').fadeOut('slow', function(){
                    $(this).remove();
                });
            }
        }
    });
   
    
}

