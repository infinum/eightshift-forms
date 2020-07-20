import { sendForm } from '../../../helpers/forms';

export class Form {
  constructor(element, {
    DATA_ATTR_FORM_TYPE,
  }) {
    this.form = element;
    this.submits = this.form.querySelectorAll('input[type="submit"]');
    this.DATA_ATTR_FORM_TYPE = DATA_ATTR_FORM_TYPE;

    // Get form type from class.
    this.formType = this.form.getAttribute(this.DATA_ATTR_FORM_TYPE);

    this.siteUrl = window.eightshiftForms.siteUrl;

    this.restRouteUrls = {
      dynamicsCrmRestUri: `${this.siteUrl}${window.eightshiftForms.dynamicsCrm.restUri}`,
    };

    this.STATE_IS_LOADING = false;
  }

  init() {
    this.form.addEventListener('submit', async (e) => {

      if (this.formType !== 'custom') {
        e.preventDefault();
      }

      if (this.formType === 'dynamics-crm') {
        this.submitForm(this.restRouteUrls.dynamicsCrmRestUri, this.getFormData(this.form));
      }
    });
  }

  async submitForm(url, data) {
    this.startLoading();
    const response = await sendForm(url, data);
    this.endLoading();
  }

  startLoading() {
    this.STATE_IS_LOADING = true;
    this.submits.forEach((submit) => {
      submit.disabled = true;
    });
    console.log('start loading');
  }

  endLoading() {
    this.STATE_IS_LOADING = false;
    this.submits.forEach((submit) => {
      submit.disabled = false;
    });
    console.log('end loading');
  }

  /**
   * Returns all form fields as { key, value } objects.
   */
  getFormData(form) {
    return [...form.elements].filter((formElem) => formElem.name).map((formElem) => ({ key: formElem.name, value: formElem.value }));
  }
}
