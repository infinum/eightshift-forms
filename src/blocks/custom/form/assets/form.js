import { sendForm } from '../../../helpers/forms';

export class Form {
  constructor(element, {
    DATA_ATTR_FORM_TYPE,
  }) {
    this.formWrapper = element;
    this.form = element.querySelector('.js-block-form-form');
    this.formId = element.getAttribute('id');
    this.spinner = element.querySelector('.js-spinner');
    this.submits = this.form.querySelectorAll('input[type="submit"]');
    this.formMessageSuccess = this.formWrapper.querySelector('.js-form-message--success');
    this.formMessageError = this.formWrapper.querySelector('.js-form-message--error');
    this.overlay = this.formWrapper.querySelector('.js-form-overlay');
    this.basicCaptchaField = this.form.querySelector('.js-block-captcha');
    this.DATA_ATTR_FORM_TYPE = DATA_ATTR_FORM_TYPE;

    // Get form type from class.
    this.formType = this.form.getAttribute(this.DATA_ATTR_FORM_TYPE);

    this.siteUrl = window.eightshiftForms.siteUrl;

    this.restRouteUrls = {
      dynamicsCrmRestUri: `${this.siteUrl}${window.eightshiftForms.dynamicsCrm.restUri}`,
      sendEmailRestUri: `${this.siteUrl}${window.eightshiftForms.sendEmail.restUri}`,
    };

    this.formAccessibilityStatus = {
      loading: window.eightshiftForms.content.formLoading,
      success: window.eightshiftForms.content.formSuccess,
    };

    this.STATE_IS_LOADING = false;
    this.CLASS_FORM_SUBMITTING = 'form-submitting';
    this.CLASS_HIDE_SPINNER = 'hide-spinner';
    this.CLASS_HIDE_MESSAGE = 'hide-form-message';
    this.CLASS_HIDE_OVERLAY = 'hide-form-overlay';
  }

  init() {
    this.form.addEventListener('submit', async (e) => {

      if (this.formType !== 'custom') {
        e.preventDefault();
      }

      if (this.formType === 'dynamics-crm') {
        this.submitForm(this.restRouteUrls.dynamicsCrmRestUri, this.getFormData(this.form));
      }

      if (this.formType === 'email') {
        this.submitForm(this.restRouteUrls.sendEmailRestUri, this.getFormData(this.form));
      }
    });
  }

  async submitForm(url, data) {
    this.startLoading();
    const response = await sendForm(url, data);
    const isSuccess = response && response.code && response.code === 200;
    this.endLoading(isSuccess, response);
  }

  startLoading() {
    this.STATE_IS_LOADING = true;
    this.form.classList.add(this.CLASS_FORM_SUBMITTING);
    this.spinner.classList.remove(this.CLASS_HIDE_SPINNER);
    this.overlay.classList.remove(this.CLASS_HIDE_OVERLAY);
    this.spinner.innerHTML = `<p>${this.formAccessibilityStatus.loading}</p>`;
    [this.formMessageSuccess, this.formMessageError].forEach((msgElem) => msgElem.classList.add(this.CLASS_HIDE_MESSAGE));

    this.submits.forEach((submit) => {
      submit.disabled = true;
    });
  }

  endLoading(isSuccess, response) {
    const state = isSuccess ? 'success' : 'error';
    this.STATE_IS_LOADING = false;
    this.form.classList.remove(this.CLASS_FORM_SUBMITTING);
    this.spinner.classList.add(this.CLASS_HIDE_SPINNER);
    this.overlay.classList.add(this.CLASS_HIDE_OVERLAY);
    this.spinner.innerHTML = `<p>${this.formAccessibilityStatus[state]}</p>`;
    this.submits.forEach((submit) => {
      submit.disabled = false;
    });

    if (isSuccess) {
      this.formMessageSuccess.classList.remove(this.CLASS_HIDE_MESSAGE);
    } else {
      this.formMessageError.textContent = response.message;
      this.formMessageError.classList.remove(this.CLASS_HIDE_MESSAGE);
    }
  }

  /**
   * Returns all form fields as { key, value } objects.
   */
  getFormData(form) {
    return [...form.elements].filter((formElem) => formElem.name).map((formElem) => ({ key: formElem.name, value: formElem.value }));
  }
}
