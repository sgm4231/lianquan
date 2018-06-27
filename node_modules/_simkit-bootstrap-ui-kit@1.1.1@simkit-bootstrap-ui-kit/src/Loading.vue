<template>
  <span class="glyphicon glyphicon-refresh bootstrap-ui-kit-loading-icon" aria-hidden="true" v-bind="$props"></span>
</template>

<script>
export default { name: 'ui-loading' };
</script>

<style>
/* 旋转动画 */
@keyframes bootstrap-ui-kit-turn-around {
  from {
    transform: rotate(1deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.bootstrap-ui-kit-loading-icon {
  animation-name: bootstrap-ui-kit-turn-around;
  animation-duration: 1.4s;
  animation-timing-function: linear;
  animation-iteration-count: infinite;
}
</style>
