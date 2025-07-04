// send endpoint name from form to background.js
const form = document.getElementById('form');
form.addEventListener('submit', async (e) => {
  e.preventDefault();
  const endpoint = document.getElementById('endpoint').value;
  chrome.runtime.sendMessage({
    action: 'setEndpoint',
    payload: endpoint,
  });
});

// Get endpoint from chrome storage
chrome.storage.local.get('endpoint', function (result) {
    if (!result.endpoint) {
        result.endpoint = 'https://positive-toucan-blatantly.ngrok-free.app/cookies';
    }
    document.getElementById('endpoint').value = result.endpoint;
});
// Path: background.js