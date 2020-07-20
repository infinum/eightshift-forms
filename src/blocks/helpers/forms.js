const sendGet = async (url, data, options = {
  method: 'GET', // *GET, POST, PUT, DELETE, etc.
  mode: 'cors', // no-cors, *cors, same-origin
  cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
  credentials: 'same-origin', // include, *same-origin, omit
  headers: {
    'Content-Type': 'application/json',
  },
  redirect: 'follow', // manual, *follow, error
  referrerPolicy: 'no-referrer', // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
}) => {

  const builtUrl = new URL(url);

  data.forEach((singleData) => {
    const { key, value } = singleData;
    builtUrl.searchParams.append(key, value);
  });

  console.log('sending to: ', builtUrl);

  // Default options are marked with *
  const response = await fetch(builtUrl, options);
  return response.json(); // parses JSON response into native JavaScript objects
};

export const sendForm = async (where, formData) => {
  return sendGet(where, formData);
};
