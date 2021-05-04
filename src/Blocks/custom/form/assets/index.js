import domReady from '@wordpress/dom-ready';

domReady(() => {
  const formSelector = '.js-form';
  const forms = document.querySelectorAll(formSelector);

  if (forms.length) {
    import('./form').then(({ Form }) => {
      forms.forEach((formElem) => {
        const form = new Form(formElem, {
          DATA_ATTR_IS_FORM_COMPLEX: 'data-is-form-complex',
          DATA_ATTR_FORM_TYPE: 'data-form-type',
          DATA_ATTR_FORM_TYPES_COMPLEX: 'data-form-types-complex',
          DATA_ATTR_FORM_TYPES_COMPLEX_REDIRECT: 'data-form-types-complex-redirect',
        });
        form.init();
      });
    });
  }
});
