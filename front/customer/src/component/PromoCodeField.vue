<template>
  <div class="promocode">
    <div class="content">
      <span class="title">
        {{ $t('cart.promocode.iHave') }}
<!--        <i class="tooltip mdi mdi-help-circle"/>-->
      </span>

      <div class="promocode-input">
        <BaseInput
          style="flex:2;border-top-right-radius:0px;border-bottom-right-radius:0px;"
          :background="'rgba(255,255,255,0.12)'"
          :placeholder="$t('cart.promocode.promocode')"
          :light="true"
        >
          <template v-slot:icon>
            <i class="mdi mdi-gift"/>
          </template>
        </BaseInput>
        <div class="activate">{{$t('cart.promocode.apply')}}</div>
      </div>
    </div>
  </div>
</template>

<script>
import BaseInput from "./../component/BaseInput";

export default {
  components: {
    BaseInput
  }
};
</script>

<style lang="scss" scoped>
.promocode {
  padding: 15px 25px;
  background: #000;
  border-radius: 8px;
  background: linear-gradient(120deg, #0177fd 0%, #65adff 100%);
  position: relative;

  &:before {
    content: url("../asset/images/promocode.png");
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
  }

  @media screen and (max-width: 1000px) {
    &:before {
      display: none;
    }
  }
}

.promocode-input {
  display: flex;
  flex: 2;
  height: 44px;
  margin: 0 0 0 21px;

  @media screen and (max-width: 1000px) {
    flex-direction: column;
    height: auto;
    width: 100%;
    margin: 0 auto;
  }
}

.content {
  position: relative;
  z-index: 3;
  display: flex;
  justify-content: space-between;
  align-items: center;

  @media screen and (max-width: 1000px) {
    flex-direction: column;
    align-items: center;
  }

  .title {
    font-size: 16px;
    font-weight: 500;
    color: #fff;
    display: flex;
    align-items: center;

    @media screen and (max-width: 1000px) {
      padding-bottom: 20px;
    }
  }

  .activate {
    border: none;
    padding: 14px;
    color: #1c1c1c;
    font-size: 14px;
    background: #ffe400;
    border-top-right-radius: 8px;
    border-bottom-right-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    text-align: center;

    @media screen and (max-width: 1000px) {
      margin-top: 10px;
      border-radius: 8px;
      text-align: center;
    }
  }

  i {
    font-size: 18px;
    color: #fff;
  }
}

.tooltip {
  opacity: 0.3;
  margin-left: 10px;
  cursor: pointer;
  transition: 0.3s;

  &:hover {
    opacity: 1;
  }
}
</style>
