<!DOCTYPE html>
<html>
<head>
	<title>ALI</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
</head>
<body>
	<div id="app" class="container" style="margin-top:70px;padding-bottom: 50%">
			<div class="col-xs-6" style="position:relative">
				<div v-if="media.length > 0">
					<transition
						v-show="isReady"
						v-for="(m, index) in media"
						:key="index"
						name="image_preview"
						enter-active-class="animated zoomInRight"
						leave-active-class="animated zoomOutLeft"
						v-on:after-enter="nextImage"
						v-on:before-leave="player('next')"
						:duration="10000"
						appear>
						<div v-show="currImage == index">
							<img width="80%"
								:src="m.images.standard_resolution.url"
								style="position:absolute;margin-right: auto;margin-left: auto">
						</div>
					</transition>
				</div>
				<div v-else-if="instagram_access_token != ''">
					<button class="btn btn-default" type="submit" @click="getMedia">Get Media</button>
				</div>
				<div v-else>
					<a href="https://api.instagram.com/oauth/authorize/?client_id=05bbec75260c49e39a31d29ac70c5e9d&redirect_uri=http://localhost:8000/instagram&response_type=token" class="animated bounce infinite">Login to Instagram</a>
				</div>
			</div> <!-- ./col-xs-6 -->
			<div class="col-xs-6">
				<div v-if="tracks.length > 0">
					<button class="btn btn-default" type="submit" @click="player('play')">Play</button>
					<button class="btn btn-default" type="submit" @click="player('pause')">Pause</button>
					<button class="btn btn-default" type="submit" @click="player('next')">Next</button>
					<button class="btn btn-default" type="submit" @click="player('previous')">Previous</button>
				</div>
				<div v-else-if="spotify_access_token != ''">
					<button class="btn btn-default" type="submit" @click="getRecommendations">Get Recommendations</button>
				</div>
				<div v-else >
					<a href="https://accounts.spotify.com/authorize?client_id=a098ae57354344d590507e14251a9762&redirect_uri=http://localhost:8000/spotify&scope=user-modify-playback-state%20user-top-read&response_type=token">Login to Spotify</a>
				</div>
			</div> <!-- ./col-xs-6 -->
	</div>
	<div class="col-xs-12">
			<p style="text-align:center">Lucas Biagini Silva | biaginisilva@gmail.com | <a href="/note" title="note">note</a></p>
		</div><!-- /App footer -->
</body>
<script
  src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.16.2/axios.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.2.6/vue.js"></script>
<script>
	
Vue.config.devtools = true;

$(document).ready(function(){

	var saveMedia = function (json) {
		console.log(json);
	};

	var app = new Vue({
		el: '#app',
		data: {
			instagram_access_token: '',
			spotify_access_token: '',
			media: [],
			images: [],
			tracks: [],
			features: [],
			currImage: -1,
			isReady: false,
		},
		created: function () {
			var self = this;

			if (window.location.pathname == '/instagram') {
				self.instagram_access_token = window.location.hash.substring(14);
				window.localStorage.instagram_access_token = self.instagram_access_token;
			} else if (window.localStorage.instagram_access_token != null){
				self.instagram_access_token = window.localStorage.instagram_access_token;
			}

			if (window.location.pathname == '/spotify') {
				//get spotify access_token from uri and save to localStorage
				let start = 14;
				let end = window.location.hash.indexOf('&token_type');
				let timestamp = Math.floor(Date.now() / 1000) + 3600;
				self.spotify_access_token = window.location.hash.substring(start, end);
				window.localStorage.spotify_access_token = self.spotify_access_token;
				window.localStorage.spotify_access_token_expiration = timestamp;
			} else if (window.localStorage.spotify_access_token != null) {
				let timestamp = Math.floor(Date.now()/1000);
				if (timestamp < window.localStorage.spotify_access_token_expiration) {
					self.spotify_access_token = window.localStorage.spotify_access_token;
				}
			}
		},
		methods: {
			getMedia: function () {
				var self = this;
				var data = {
					access_token: self.instagram_access_token,
				};
				var url = '/getMedia';
				axios.post(url, data)
					.then(function(response){
						self.media = response.data;
						console.log(self.media);
					})
					.catch(function(error){
						console.log(error);
					});
			},
			getRecommendations: function (features, index) {
				var self = this;
				var data = {
					access_token: self.spotify_access_token,
					features: features,
				};
				var url = '/recommendations';
				axios.post(url, data)
					.then(function(response){
						//self.tracks = response.data.tracks;
						//self.tracks.push(response.data.tracks);
						self.tracks[index] = response.data.tracks[0];
						console.log(self.recommendations);

						if (index == 19) {
							self.startPlayer();
						}
					})
					.then(function(){
						//self.startPlayer();
					})
					.catch(function(error){
						console.log(error);
					});
			},
			nextImage:function () {
				var self = this;
				if (self.currImage < self.media.length - 1) {
					self.currImage = self.currImage + 1;
				} else {
					self.currImage = 0;
				}
			},
			start: function () {
				var self = this;
				self.isReady = true;
				self.currImage = 0;
			},
			getPixels: function(media) {
				var self = this;

				for (var i = 0; i < media.length; i++) {
					let img = new Image(media[i].images.standard_resolution.width, media[i].images.standard_resolution.height);
					img.crossOrigin = "Anonymous";
					img.src = media[i].images.standard_resolution.url;
					img.onload = function () {
						let canvas = document.createElement('canvas');
						let context = canvas.getContext('2d');
						context.drawImage(img, 0, 0 );
						let imageData = context.getImageData(0, 0, img.width, img.height).data;
						//self.images.push(myData);
						self.features.push(self.getFeatures(imageData));
					}
				}

				console.log(self.images);
			},
			getFeatures: function (img) {
				console.log(img);
				let features = {
					acousticness: null,
					danceability: null,
					energy: null,
					valence: null
				};
				let redCount = 0;
				let greenCount = 0;
				let blueCount = 0;
				for (i = 0; i < img.length; i = i + 4) {

					let red = img[i];
					let green = img[i+1];
					let blue = img[i+2];
					let whatever = img[i+3];

					if (red ==0 && green == 0 && blue == 0 && whatever == 0) break;
					
					redCount += red/100;
					greenCount += green/100;
					blueCount += blue/100;
				}
				console.log(redCount+" "+greenCount+" "+blueCount);

				//acousticness green
				let acousticness = greenCount;
				let acousticness_factor = 100;
				if (acousticness > 100) {
					acousticness_factor = 1000;
				}
				features.acousticness = (acousticness / acousticness_factor).toFixed(1);

				//energy red
				let energy = redCount;
				let energy_factor = 100;
				if (energy > 100) energy_factor = 1000;
				features.energy = (energy / energy_factor).toFixed(1);

				//valence blue
				let valence = blueCount;
				let valence_factor = 100;
				if (valence > 100) valence_factor = 1000;
				features.valence = (valence / valence_factor).toFixed(1);

				//danceability
				let danceability = (redCount + greenCount + blueCount) / 3
				let danceability_factor = 100;
				if (danceability > 100) danceability_factor = 1000;
				features.danceability = (danceability / danceability_factor).toFixed(1);


				return features;
			},
			player: function (action) {
				var self = this;
				var url = '/player';
				var data = {
					access_token: self.spotify_access_token,
					action: action
				};
				axios.post(url, data)
					.then(function(response){
						console.log(response);
					})
					.catch(function(error){
						console.log(error);
					});
			},
			seek: function (position) {
				var self = this;
				var url = '/seek';
				var data = {
					access_token: self.spotify_access_token,
					position: position,
				};
				axios.post(url, data)
					.then(function(response){
						console.log(response.data);
					})
					.catch(function(error){
						console.log(error);
					})
			},
			startPlayer: function () {
				var self = this;
				var url = '/start';
				var data = {
					access_token: self.spotify_access_token,
					tracks: self.tracks
				};
				axios.post(url, data)
					.then(function(response){
						console.log(response);
						self.start();
					})
					.catch(function(error){
						console.log(error);
					});
			}
		},
		watch: {
			media: function (val) {
				var self = this;
				self.getPixels(val);
			},
			features: function(val) {
				var self = this;
				self.tracks.push(null);
				self.getRecommendations(val[val.length-1], val.length-1);
			}
		}
	});
});

</script>
</html>