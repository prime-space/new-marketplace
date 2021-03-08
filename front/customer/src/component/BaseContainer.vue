<template>
  <div class="bordered-container">
    <slot></slot>
  </div>
</template>

<script>
export default {};
</script>

<style scoped lang="scss">
.bordered-container {
  padding: 20px;
  border: 4px solid rgba(#000, 0.06);
  border-radius: 0 8px 8px 8px;

  @media screen and (max-width:700px){
    padding: 10px;
  }

  @media screen and (max-width:500px){
    padding: 5px;
  }
}
</style>
