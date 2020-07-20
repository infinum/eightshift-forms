import domReady from '@wordpress/dom-ready';

domReady(() => {
  const formSelector = '.js-form';
  const forms = document.querySelectorAll(formSelector);

  if (forms.length) {
    import('./form').then(({ Form }) => {
      forms.forEach((formElem) => {
        const form = new Form(formElem, {
          DATA_ATTR_FORM_TYPE: 'data-form-type',
        });
        form.init();
      });
    });
  }
});
