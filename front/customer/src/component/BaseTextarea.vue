<template>
  <div class="textarea">
    <textarea :placeholder="placeholder"></textarea>
  </div>
</template>

<script>
export default {
  props:{
    placeholder:{
      type:String,
      default:''
    }
  }
}
</script>

<style lang="scss" scoped>
  .textarea{
    width: 100%;

    textarea{
      width: 100%;
      height: 110px;
      padding: 20px;
      box-sizing: border-box;
      resize: none;
      background: #FAFAFA;
      border-radius: 8px;
      border: none;
      outline: none;
      font-weight: 500;
      font-size: 15px;

      &::placeholder{
        color: #A8A8A8;
        opacity: 0.35;
        font-weight: 500;
        font-size: 15px;
      }
    }
  }
</style>
