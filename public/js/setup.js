(function () {
  var divs = document.querySelectorAll('div[data-url]');
  for (var i = 0; i < divs.length; i++) {
    var div = divs[i];
    sendRequest(div)
  }

  function sendRequest(div) {
    var request = new XMLHttpRequest();
    request.open('get', div.getAttribute('data-url'), true);
    request.onload = function () {
      div.innerHTML = request.responseText;
    };
    request.onerror = function () {
      div.innerHTML = 'An error occurred while loading :(';
    };
    request.send();
  }
}());
