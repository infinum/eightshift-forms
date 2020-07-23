import { sendForm } from '../../../helpers/forms';

export class Form {
  constructor(element, {
    DATA_ATTR_FORM_TYPE,
  }) {
    this.form = element;
    this.formId = element.getAttribute('id');
    this.spinner = document.querySelector(`[data-parent-form=${this.formId}]`);
    this.submits = this.form.querySelectorAll('input[type="submit"]');
    this.DATA_ATTR_FORM_TYPE = DATA_ATTR_FORM_TYPE;

    // Get form type from class.
    this.formType = this.form.getAttribute(this.DATA_ATTR_FORM_TYPE);

    this.siteUrl = window.eightshiftForms.siteUrl;

    this.restRouteUrls = {
      dynamicsCrmRestUri: `${this.siteUrl}${window.eightshiftForms.dynamicsCrm.restUri}`,
    };

    this.formAccessibilityStatus = {
      loading: window.eightshiftForms.content.formLoading,
      success: window.eightshiftForms.content.formSuccess,
    };

    this.STATE_IS_LOADING = false;
    this.CLASS_HIDE_FORM = 'hide-form';
    this.CLASS_HIDE_SPINNER = 'hide-spinner';
  }

  init() {
    console.log('Form is: ', this.form);
    this.form.addEventListener('submit', async (e) => {
      console.log('submitting form');

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
    this.form.classList.add(this.CLASS_HIDE_FORM);
    this.spinner.classList.remove(this.CLASS_HIDE_SPINNER);
    this.spinner.innerHTML = `<p>${this.formAccessibilityStatus.loading}</p>`;
    
    this.submits.forEach((submit) => {
      submit.disabled = true;
    });
  }

  endLoading() {
    this.STATE_IS_LOADING = false;
    this.form.classList.remove(this.CLASS_HIDE_FORM);
    this.spinner.classList.add(this.CLASS_HIDE_SPINNER);
    this.spinner.innerHTML = `<p>${this.formAccessibilityStatus.success}</p>`;
    this.submits.forEach((submit) => {
      submit.disabled = false;
    });
  }

  /**
   * Returns all form fields as { key, value } objects.
   */
  getFormData(form) {
    return [...form.elements].filter((formElem) => formElem.name).map((formElem) => ({ key: formElem.name, value: formElem.value }));
  }
}
