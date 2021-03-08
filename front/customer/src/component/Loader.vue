<template>
  <div class="loader" v-if="isLoading">
    <div class="spinner-wrapper">
      <div class="spinner"></div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    isLoading: {
      default: true,
      type: Boolean
    }
  }
};
</script>

<style lang="scss" scoped>
/*@keyframes spin {*/
  /*0% {*/
  /*  transform: rotate(0deg);*/
  /*}*/
  /**/
  /*100% {*/
  /*  transform: rotate(360deg);*/
  /*}*/
/*}*/

.loader {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 999;

  @media screen and (max-width: 900px) {
    margin: 30px 0 0;
  }
}

.spinner {
  position: relative;
  display: block;
  margin: 0 auto;
  font-size: 46px;
  border: 2px solid #4dd29b;

  @media screen and (max-width: 900px) {
    color: #d3722e;
    font-size: 30px;
  }
}

.spinner-wrapper {
  text-align: center;
  animation: spin 1.5s ease infinite;
}

p {
  margin-top: 25px;

  @media screen and (max-width: 650px) {
    text-align: center;
  }
}
</style>
