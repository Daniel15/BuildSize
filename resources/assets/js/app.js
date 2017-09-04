const csrfToken = document.getElementById('csrf-token').content;

function loadDivs() {
  const divs = document.querySelectorAll('div[data-url]');
  for (let i = 0; i < divs.length; i++) {
    sendRequest(divs[i]);
  }

  function sendRequest(div) {
    const request = new XMLHttpRequest();
    request.open('get', div.getAttribute('data-url'), true);
    request.onload = function () {
      div.innerHTML = request.responseText;
    };
    request.onerror = function () {
      div.innerHTML = 'An error occurred while loading :(';
    };
    request.send();
  }
}

function handleLogoutLink() {
  const logoutLinkEl = document.getElementById('logout-link');
  if (!logoutLinkEl) {
    return;
  }
  logoutLinkEl.addEventListener('click', evt => {
    evt.preventDefault();

    const formEl = document.createElement('form');
    formEl.method = 'post';
    formEl.action = logoutLinkEl.href;
    const csrfEl = document.createElement('input');
    csrfEl.name = '_token';
    csrfEl.value = csrfToken;
    formEl.appendChild(csrfEl);
    document.body.appendChild(formEl);
    formEl.submit();
  }, false);
}

handleLogoutLink();
loadDivs();
