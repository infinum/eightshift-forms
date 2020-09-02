import domReady from '@wordpress/dom-ready';

domReady(() => {
  const inputSelector = '.js-block-input';
  const inputs = document.querySelectorAll(inputSelector);

  if (inputs.length) {
    import('./input').then(({ Input }) => {
      inputs.forEach((inputElem) => {
        const form = new Input(inputElem);
        form.init();
      });
    });
  }
});
