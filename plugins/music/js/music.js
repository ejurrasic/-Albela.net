function change_music_source(t) {
    var o = $(t);
    var v = o.val();
    $(".music-source-selector .source").hide();
    $(".music-source-selector  ." + v).fadeIn();
    if(v == 'upload') {
        $('.music-details-container').show();
    } else {
        $('.music-details-container').hide();
    }
    return true;
}
function music_set_list_type(type) {
    $.ajax({url: baseUrl + 'music/ajax?action=set_list_type&type=' + type + '&csrf_token=' + requestToken});
}
if (typeof music === "undefined") {
    window.music = {}
}
if (!music.player) {
    music.player = {};
}
music.player = {
    playing : false,
    muted : false,
    duration : 0,
    volumeState: 1,
    repeat: false,
    init : function(playlist, options) {
//Destroy any currently playing music (if any).
        music.destroy();
//Set Playlist
        this.playlist = playlist;
        for(var i in this.playlist) {
            this.playlist[i]['cover_art'] = this.playlist[i]['cover_art'] ? this.playlist[i]['cover_art'] : 'plugins/music/images/preview.png';
        }
//Set options
        this.options = options;
        this.nowPlaying = this.options.nowPlaying || Object.keys(this.playlist)[0];
        this.autoplay = options.autoplay || false;
//Create HTML Tags and Set their Attributes
        this.container = document.getElementById('music-player-container');
        this.musicPlayer = document.createElement('div');
        this.musicPlayer.className = 'music-player';
        this.player = document.createElement('div');
        this.player.className = 'player gradient row';
        this.playerLeft = document.createElement('div');
        this.playerLeft.className = 'col-sm-2  player-left';
        this.playerRight = document.createElement('div');
        this.playerRight.className = 'col-sm-10 player-right';
        this.cover = document.createElement('img');
        this.cover.id = 'music-player-cover';
        this.cover.className = 'cover';
        this.time = document.createElement('span');
        this.time.id = 'music-player-time';
        this.time.className = 'time';
        this.time.innerHTML = '00:00';
        this.repeatToggle = document.createElement('i');
        this.repeatToggle.id = 'music-player-repeat-toggle';
        this.repeatToggle.className = ' button toggle off ion-android-refresh';
        this.info = document.createElement('span');
        this.info.id = 'music-player-info';
        this.info.className = 'info';
        this.info.innerHTML = 'Unknown Artist - Unknown Title';
        this.cover.src = 'plugins/music/images/preview.png';
        this.play = document.createElement('i');
        this.play.id = 'music-player-play-pause-button';
        this.play.className = 'button gradient ion-play';
        this.next = document.createElement('i');
        this.next.id = 'music-player-next-button';
        this.next.className = 'button gradient ion-ios-skipforward';
        this.prev = document.createElement('i');
        this.prev.id = 'music-player-prev-button';
        this.prev.className = 'button gradient ion-ios-skipbackward';
        this.mute = document.createElement('i');
        this.mute.id = 'music-player-mute';
        this.mute.className = 'button gradient ion-android-volume-up';
        this.volume = document.createElement('input');
        this.volume.id = 'music-player-volume';
        this.volume.type = 'range';
        this.volume.value = 100;
        this.seek = document.createElement('input');
        this.seek.id = 'music-player-seek';
        this.seek.type = 'range';
        this.seek.value = 0;
        this.stop = document.createElement('i');
        this.stop.className = 'button gradient ion-stop';
        this.song = new Audio(this.playlist[this.nowPlaying]['file_path']);
        this.song.type = 'audio/mpeg';
        this.song.src = this.playlist[this.nowPlaying]['file_path'];
        this.song.volume = music.player.volumeState || 1;
        this.song.load();
        this.song.pause();
        if (!isNaN(this.song.duration)) {
            this.song.currentTime = 0;
        }
        this.playing = false;
        this.paused = false;
//Arrange HTML
        this.duration = this.song.duration;
        this.playerLeft.appendChild(this.cover);
        this.playerLeft.appendChild(this.prev);
        this.playerLeft.appendChild(this.play);
        this.playerLeft.appendChild(this.next);
        this.playerRight.appendChild(this.info);
        this.playerRight.appendChild(this.time);
        this.playerRight.appendChild(this.repeatToggle);
        this.playerRight.appendChild(this.mute);
        this.playerRight.appendChild(this.volume);
        this.playerRight.appendChild(this.seek);
        this.playerRight.appendChild(this.stop);
        this.player.appendChild(this.playerLeft);
        this.player.appendChild(this.playerRight);
        this.musicPlayer.appendChild(this.player);
        this.container.appendChild(this.musicPlayer);
        this.play.className = this.play.className.replace( /(?:^|\s)ion-pause(?!\S)/g , ' ion-play ');
//Raise chat boxes container
        if(document.getElementById('chat-boxes-container')){
            document.getElementById('chat-boxes-container').style.bottom = '67px';
        }
//Show Music Player
        this.container.style.display = 'block';
//Add Events
        this.song.addEventListener('loadedmetadata', function() {
            this.currentTime = 0;
        }, false);
        this.song.addEventListener('ended', function() {
            if (music.player.repeat == 'one') {
                music.player.init(music.player.playlist, {nowPlaying: music.player.nowPlaying, autoplay: true});
            } else {
                var music_ids = Object.keys(music.player.playlist);
                var playlist = music.player.playlist;
                for(var i in music_ids) {
                    if(music.player.playlist[music_ids[i]]['slug'] == music.player.nowPlaying && (i < music_ids.length - 1)) {
                        var nextId = music_ids[(parseInt(i) + 1)];
                        var options = {nowPlaying: nextId, autoplay: true}
                        music.player.init(playlist, options);
                        return true;
                    }
                }
                if(music.player.repeat == 'all') {
                    nextId = music_ids[0];
                    options = {nowPlaying: nextId, autoplay: true}
                    music.player.init(playlist, options);
                    return true;
                } else {
                    music.player.stop.click();
                    return true;
                }
            }
        });
        this.play.addEventListener('click', function(event) {
            event.preventDefault();
            if(!music.player.playing || music.player.paused){
                music.player.song.play();
                music.player.playing = true;
                $.ajax({url: baseUrl + 'music/ajax?action=music_played&id=' + music.player.nowPlaying + '&csrf_token=' + requestToken});
                music.player.paused = false;
                music.updateDisplay(music.player.playlist[music.player.nowPlaying]);
                this.className = this.className.replace( /(?:^|\s)ion-play(?!\S)/g , ' ion-pause ');
                music.player.seek.max = music.player.song.duration;
            } else {
                music.player.song.pause();
                music.player.paused = true;
                music.updateDisplay(music.player.playlist[music.player.nowPlaying]);
                this.className = this.className.replace( /(?:^|\s)ion-pause(?!\S)/g , ' ion-play ')
            }
        });
        this.next.addEventListener('click', function(event) {
            var music_ids = Object.keys(music.player.playlist);
            var playlist = music.player.playlist;
            for(var i in music_ids) {
                if(music.player.playlist[music_ids[i]]['slug'] == music.player.nowPlaying && (i < music_ids.length - 1)) {
                    var nextId = music_ids[(parseInt(i) + 1)];
                    var options = {nowPlaying: nextId, autoplay: true}
                    music.player.init(playlist, options);
                    return true;
                }
            }
        });
        this.prev.addEventListener('click', function(event) {
            var music_ids = Object.keys(music.player.playlist);
            var playlist = music.player.playlist;
            for(var i in music_ids) {
                if(music.player.playlist[music_ids[i]]['slug'] == music.player.nowPlaying && i != 0) {
                    var prevId = music_ids[(parseInt(i) - 1)];
                    var options = {nowPlaying: prevId, autoplay: true}
                    music.player.init(playlist, options);
                    return true;
                }
            }
        });
        this.mute.addEventListener('click', function(event) {
            event.preventDefault();
            if(!music.player.muted){
                music.player.song.volume = 0;
                music.player.volumeState = music.player.song.volume;
                music.player.muted = true;
                this.className = this.className.replace( /(?:^|\s)ion-android-volume-up(?!\S)/g , ' ion-android-volume-mute ')
            }
            else {
                music.player.song.volume = 1;
                music.player.volumeState = music.player.song.volume;
                music.player.muted = false;
                this.className = this.className.replace( /(?:^|\s)ion-android-volume-mute(?!\S)/g , ' ion-android-volume-up ')
            }
        });
        this.stop.addEventListener('click', function(event) {
            event.preventDefault();
            music.player.song.pause();
            music.player.playing = false;
            music.player.song.currentTime = 0;
            music.player.play.className = music.player.play.className.replace( /(?:^|\s)ion-pause(?!\S)/g , ' ion-play ');
            music.updateDisplay();
            if(/floating/.test(music.player.container.className)) {
                music.player.container.style.display = 'none';
                if(document.getElementById('chat-boxes-container')){
                    document.getElementById('chat-boxes-container').style.bottom = '0px';
                }
            }
        });
        this.seek.addEventListener('change', function(event) {
            music.player.song.currentTime = $(music.player.seek).val();
            this.max = music.player.song.duration;
        });
        this.song.addEventListener('timeupdate',function (event) {
            if(typeof this.currentTime !== null) {
                music.player.seek.max = music.player.song.duration;
                music.player.seek.value = parseInt(this.currentTime, 10);
                document.getElementById('music-player-time').innerHTML = ('00' + Math.floor(parseInt(this.currentTime, 10) / 60)).slice(-2) + ':' + ('00' + (parseInt(this.currentTime, 10) % 60)).slice(-2);
            }
        });
        this.volume.addEventListener('change', function(event) {
            music.player.song.volume = (music.player.volume.value / 100);
            music.player.volumeState = music.player.song.volume;
        });
        this.song.addEventListener('volumechange',function (event) {
            if(typeof this.volume !== null) {
                music.player.volume.value = this.volume * 100;
                music.updateDisplay();
            }
        });
        this.repeatToggle.addEventListener('click', function(event) {
            if(/one/.test(this.className)) {
                music.player.repeat = 'all';
                this.className = this.className.replace(/one/g, 'all');
                return true;
            }
            if(/off/.test(this.className)) {
                music.player.repeat = 'one';
                this.className = this.className.replace(/off/g, 'one');
                return true;
            }
            if(/all/.test(this.className)) {
                music.player.repeat = false;
                this.className = this.className.replace(/all/g, 'off');
                return true;
            }
        });
//Autoplay
        if(this.autoplay){
            try {
                this.play.click();
            } catch (e) {
                console.log('This browser does not support autoplay');
            }
        }
    }
}
if (!music.destroy) {
    music.destroy = function () {
        if(typeof(music.player.song) == "object") {
            music.player.container.innerHTML = '';
            music.player.song.pause();
            music.player.song = new Audio();
        }
    }
}
if (!music.updateDisplay) {
    music.updateDisplay = function (track) {
        var trackList = $('.playing');
        for(var i in trackList) {
            var trackId = typeof trackList[i] !== 'undefined' ? trackList[i].id : false;
            if(trackId) {
                document.getElementById(trackId).className = document.getElementById(trackId).className.replace(/(?:^|\s)playing(?!\S)/g , '');
                if(/ion-pause/.test(document.getElementById(trackId).className)) {
                    document.getElementById(trackId).className = document.getElementById(trackId).className.replace(/(?:^|\s)ion-pause(?!\S)/g, ' ion-play ');
                }
            }
        }
        if(typeof music.player.playing !== 'undefined' && music.player.playing) {
            track = track || music.player.playlist[music.player.nowPlaying];
            trackId = 'track-' + track.id;
            document.getElementById('music-player-cover').src = baseUrl + track.cover_art.replace(/_%w_/g , '_200_').replace(/\d+\[cdn\]/g , '');
            document.getElementById('music-player-info').innerHTML = track.artist == '' ? track.title : track.artist + ' - ' + track.title;
            if(document.getElementById(trackId)) {
                document.getElementById(trackId).className += ' playing';
            }
            var trackPageBgId = 'music-display';
            if(document.getElementById(trackPageBgId)) {
                document.getElementById(trackPageBgId).style.backgroundImage = "url('" + baseUrl + track.cover_art.replace(/_%w_/g , '_600_') + "')";
            }
            var trackPageCoverId = 'track-page-cover';
            if(document.getElementById(trackPageCoverId)) {
                document.getElementById(trackPageCoverId).src = baseUrl + track.cover_art.replace(/_%w_/g , '_200_').replace(/\d+\[cdn\]/g , '');
            }
            var trackPageInfoId = 'track-page-info';
            if(document.getElementById(trackPageInfoId)) {
                document.getElementById(trackPageInfoId).innerHTML = '<div>Title: ' + track.title + '</div>' + '<div>Artist: ' + track.artist + '</div>' + '<div>Album: ' + track.album + '</div></div>';
            }
            var listPlayButton = 'list-play-button-' + track.id;
            if (document.getElementById(listPlayButton)) {
                var button = document.getElementById(listPlayButton);
                if (music.player.paused) {
                    button.className = button.className.replace(/(?:^|\s)ion-pause(?!\S)/g, ' ion-play ');
                } else {
                    button.className = button.className.replace(/(?:^|\s)ion-play(?!\S)/g, ' ion-pause ');
                }
                button.className += ' playing ';
            }
            var playlistPlayButton = 'playlist-play-button-' + track.id;
            if (document.getElementById(playlistPlayButton)) {
                var button = document.getElementById(playlistPlayButton);
                if (music.player.paused) {
                    button.className = button.className.replace(/(?:^|\s)ion-pause(?!\S)/g, ' ion-play ');
                } else {
                    button.className = button.className.replace(/(?:^|\s)ion-play(?!\S)/g, ' ion-pause ');
                }
                button.className += ' playing ';
            }
            var musicPageDashboard = 'music-page-dashboard';
            if(document.getElementById(musicPageDashboard)) {
                $('#' + musicPageDashboard).load(baseUrl + 'music/ajax?action=music_page_dashboard&id=' + music.player.nowPlaying + '&csrf_token=' + requestToken);
            }
            var musicPageComment = 'music-page-comment';
            if(document.getElementById(musicPageComment)) {
                $('#' + musicPageComment).load(baseUrl + 'music/ajax?action=music_page_comment&id=' + music.player.nowPlaying + '&csrf_token=' + requestToken);
            }
            var musicPlayerMute = 'music-player-mute';
            if(document.getElementById(musicPlayerMute)) {
                button = document.getElementById(musicPlayerMute);
                if (music.player.song.volume == 0) {
                    button.className = button.className.replace(/(?:^|\s)ion-android-volume-up(?!\S)/g, ' ion-android-volume-mute ');
                } else {
                    button.className = button.className.replace(/(?:^|\s)ion-android-volume-mute(?!\S)/g, ' ion-android-volume-up ');
                }
            }
            var musicPlayerRepeatToggle = 'music-player-repeat-toggle';
            if(music.player.repeat == 'all') {
                document.getElementById(musicPlayerRepeatToggle).className = document.getElementById(musicPlayerRepeatToggle).className.replace(/off|one|all/g, 'all');
            }
            if(music.player.repeat == 'one') {
                document.getElementById(musicPlayerRepeatToggle).className = document.getElementById(musicPlayerRepeatToggle).className.replace(/off|one|all/g, 'one');
            }
            if(music.player.repeat == false) {
                document.getElementById(musicPlayerRepeatToggle).className = document.getElementById(musicPlayerRepeatToggle).className.replace(/(off|one|all)/g, 'off');
            }
        }
        var musicPlayerVolume = 'music-player-volume';
        if(document.getElementById(musicPlayerVolume)) {
            var volume = document.getElementById(musicPlayerVolume);
            volume.value = music.player.song.volume * 100;
        }
    }
}

if (!music.updateButton) {
    music.updateButton = function (button) {
        document.getElementById("music-player-play-pause-button").click();
        if(music.player.paused) {
            button.className = button.className.replace(/(?:^|\s)ion-pause(?!\S)/g , ' ion-play ');
        } else {
            button.className = button.className.replace(/(?:^|\s)ion-play(?!\S)/g , ' ion-pause ');
        }
    }
}
if (!music.playlist) {
    music.playlist = {};
}
music.playlist.editor = {
    searchMusic: function(input) {
        var str = input.value;
        if(str.length >= 3) {
            $('#music-playlist-editor-search-result').fadeIn('fast');
            $.ajax({
                url: baseUrl + 'music/playlist/editor/search?term=' + str + '&csrf_token=' + requestToken,
                success: function(data) {
                    $('#music-playlist-editor-search-result').html(data);
                }
            })
        } else {
            $('#music-playlist-editor-search-result').fadeOut('fast');
        }
    },
    addMusic : function (id, title) {
        if($("#" + id) !== null) {
            this.removeMusic(id);
        }
        $("#music-items").append('' +
        '<div id="' + id + '" class="music-item">' +
        '<span class="title">' + title + '</span>' +
        '<input name="val[musics][]" value="' + id + '" type="hidden">' +
        '<a href="#" onclick="return music.playlist.editor.removeMusic(\'' + id + '\')" class="close"><i class="ion-android-close"></i></a>' +
        '</div>'
        );
        $('#music-playlist-editor-search-result').fadeOut('fast');
        return false
    },
    removeMusic : function (id) {
        $('#' + id).remove();
        return false
    }
}