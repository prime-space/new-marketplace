<template>
  <footer>
    <span>© {{ config.year }} {{config.siteName}}</span>

<!--    <div class="message">-->
<!--      <img src="./../asset/icons/message.png" alt />-->
<!--    </div>-->
  </footer>
</template>

<script>
export default {
    data() {
        return {
            config: config,
        };
    },
};
</script>

<style scoped lang="scss">
footer {
  padding: 20px 0;
  text-align: center;
  position: relative;

  @media screen and (max-width: 1100px){
    display: flex;
    flex-direction: column-reverse;
  }

  span {
    opacity: 0.6;
    font-size: 13px;
    color: #ffffff;
    text-transform: uppercase;

    @media screen and (max-width: 1100px){
      padding-top: 20px;
    }
  }

  .message {
    display: flex;
    justify-content: center;
    align-items: center;

    position: absolute;
    right: 0;
    top: -65px;

    width: 65px;
    height: 65px;
    border-radius: 50%;
    background: #ffffff;

    cursor: pointer;


    @media screen and (max-width: 1100px){
      position: static;
      margin: 10px auto 0;
    }
  }
}
</style>
