<template>
  <div class="checkbox">
    <i v-if="active" class="mdi mdi-check mdi-16px" style="color:#0177fd"/>
<!--    <img src="./../asset/icons/checked.png" alt="" v-if="active">-->
  </div>
</template>

<script>
export default {
  props: {
    active: {
      type: Boolean,
      require: true,
      default: false
    }
  }
};
</script>

<style lang="scss" scoped>
.checkbox {
  width: 16px;
  height: 16px;
  border: 2px solid #0177fd;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
