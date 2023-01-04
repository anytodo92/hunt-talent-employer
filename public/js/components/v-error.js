const errorComponent = {
    props: ['modelValue'],
    template: `
      <div class="alert alert-danger v-error" v-if="modelValue" v-html="modelValue"></div>
    `
};