<template>
  <ui-process-button v-bind="$props" @click="handleClick">
    <template slot-scope="{ processing }">
      <template v-if="processing">
        <ui-loading></ui-loading>
        {{ proceslabel }}
      </template>
      <template v-else>
        {{ label }}
      </template>
    </template>
  </ui-process-button>
</template>

<script>
import ProcessClickButton from './ProcessClickButton';
import Loading from './Loading';
export default {
  name:'ui-button',
  components: {
    [ProcessClickButton.name]: ProcessClickButton,
    [Loading.name]: Loading,
  },
  props: {
    label: { type: String, default: '提交' },
    proceslabel: { type: String, default: '提交中...' },
  },
  methods: {
    handleClick (event) {
      this.$emit('click', event);
    }
  },
};
</script>
