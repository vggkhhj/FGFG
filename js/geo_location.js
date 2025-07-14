/* Функции для определения местоположения пользователя */

//Определение местоположения пользователя. callback - функция, которая вызовется при успешном определении
function getLocation(callback,callbackError) {
	//Получаем местоположение
	var cookie = geoloc_getCookie("userLocation");
	var currentTime = parseInt(new Date().getTime() / 1000);
	if (cookie) {
		var cookie = JSON.parse(cookie);
		if (currentTime - cookie.time < 60 * 20) {	// запрашивать снова через 20 мин
			//брать из куки
			callback({
				coords:
					{
						longitude: cookie.longitude,
						latitude: cookie.latitude,
						cookie: 1
					}
			});
		}
		else cookie = null;
	}
	if (!cookie) {
		if (navigator.geolocation) {
		//   $("#GPSloader").show();
			navigator.geolocation.getCurrentPosition(callback, callbackError, { maximumAge: 60000, timeout: 10000, enableHighAccuracy: true});
		}
	}
}
function doneGPSUpdate(pos) {
	//write cookie
	if (pos.coords.cookie != 1) {
		var currentTime = parseInt(new Date().getTime() / 1000);
		geoloc_setCookie("userLocation", JSON.stringify({ time: currentTime, longitude: pos.coords.longitude, latitude: pos.coords.latitude }));
	}

	// $("#GPSloader").hide();
}
function doneGPSUpdateError(error) {
	// $("#GPSloader").hide();
	var cookie = geoloc_getCookie("userLocation");
	if (cookie) {
	  cookie = JSON.parse(cookie);        
	}
	getGeolocation_alternative();
	return cookie;
}

function getGPSDistance(p1, p2) {
	var R = 6371; // Radius of the Earth in km
	var dLat = (p2.lat() - p1.lat()) * Math.PI / 180;
	var dLon = (p2.lng() - p1.lng()) * Math.PI / 180;
	var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
	Math.cos(p1.lat() * Math.PI / 180) * Math.cos(p2.lat() * Math.PI / 180) *
	Math.sin(dLon / 2) * Math.sin(dLon / 2);
	var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
	var d = R * c;
	return d;
};

//SET COOKIE 
function geoloc_setCookie(name, value) { 
	var valueEscaped = escape(value); 
	var expiresDate = new Date(); 
	expiresDate.setTime(expiresDate.getTime() + 365 * 24 * 60 * 60 * 1000); 
	var expires = expiresDate.toGMTString(); 
	var newCookie = name + "=" + valueEscaped + "; path=/; expires=" + expires; 
	if (valueEscaped.length <= 4000) document.cookie = newCookie + ";"; 
} 
 
//GET COOKIE 
function geoloc_getCookie(name) { 
	var prefix = name + "="; 
	var cookieStartIndex = document.cookie.indexOf(prefix); 
	if (cookieStartIndex == -1) return null; 
	var cookieEndIndex = document.cookie.indexOf(";", cookieStartIndex + prefix.length); 
	if (cookieEndIndex == -1) cookieEndIndex = document.cookie.length; 
	return unescape(document.cookie.substring(cookieStartIndex + prefix.length, cookieEndIndex)); 
}

/** Резервная функция определения координат (используя Яндекс Карты) */
function getGeolocation_alternative(){
	$.getScript('http://api-maps.yandex.ru/2.0-stable/?lang=ru-RU&coordorder=longlat&load=package.full&wizard=constructor&onload=ymaploadsucces');
}

/** Загрузка данных используя Яндекс Карты */
function ymaploadsucces(ymaps) {
	var currentTime = parseInt(new Date().getTime() / 1000);
	geoloc_setCookie("userLocation", JSON.stringify({ time: currentTime, longitude: ymaps.geolocation.longitude, latitude: ymaps.geolocation.latitude, city:ymaps.geolocation.city,country:ymaps.geolocation.country,region:ymaps.geolocation.region}));
}

