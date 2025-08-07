function initWizard(root) {
  if (typeof window.Alpine === 'undefined') {
    return { initialized: false };
  }
  const initialStepKey = root.dataset.stepKey || '';
  const initialValues = JSON.parse(root.dataset.values || '{}');
  const initialErrors = JSON.parse(root.dataset.errors || '{}');
  window.Alpine.data('stitchWizard', () => ({
    stepKey: initialStepKey,
    values: initialValues,
    errors: initialErrors,
    isBusy: false,
    init() {
      root.addEventListener('ajax:before', () => { this.isBusy = true; });
      root.addEventListener('ajax:success', (event) => {
        this.isBusy = false;
        if (event.detail && event.detail.stepKey) { this.stepKey = event.detail.stepKey; }
        this.errors = {};
      });
      root.addEventListener('ajax:error', (event) => {
        this.isBusy = false;
        if (event.detail && event.detail.errors) { this.errors = event.detail.errors; this.handleValidationErrors(); }
      });
    },
    handleValidationErrors() {
      setTimeout(() => {
        const firstErrorField = root.querySelector('[aria-invalid="true"]');
        if (firstErrorField) { firstErrorField.focus(); }
      }, 100);
    },
    setValue(name, value) {
      this.values[name] = value;
      if (this.errors[name]) { delete this.errors[name]; }
    },
    addRow(fieldName) {
      if (!this.values[fieldName]) { this.values[fieldName] = []; }
      this.values[fieldName].push({});
    },
    removeRow(fieldName, index) {
      if (this.values[fieldName] && this.values[fieldName].length > index) { this.values[fieldName].splice(index, 1); }
    }
  }));
  return { initialized: true, root };
}

export default { initWizard };
