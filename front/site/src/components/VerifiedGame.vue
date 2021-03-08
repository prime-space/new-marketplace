<template>
  <div class="verified">
    <img
      class="verified__image"
      src="../assets/img/verified-image.png"
      alt=""
    />
    <div class="verified__badge"><i class="fas fa-badge-check"></i></div>
    <div class="verified__info">
      <span class="info-title">Resident Evil 3</span>
      <span class="info-seller"
        ><i class="fas fa-user-tie pr-1"></i> VladimirDesigner</span
      >
    </div>
  </div>
</template>

<script>
export default {}
</script>

<style lang="scss" scoped>
.verified {
  position: relative;
  height: 120px;
  width: 100%;

  &:before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 30%;
    border-radius: 8px;
    background: linear-gradient(0deg, rgba(0, 0, 0, 0) 0%, #000 100%);
    opacity: 0.7;
    z-index: 2;
  }

  &:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 30%;
    border-radius: 8px;
    background: linear-gradient(180deg, rgba(0, 0, 0, 0) 0%, #000 100%);
    opacity: 0.7;
    z-index: 2;
  }

  &__image {
    position: absolute;
    width: 100%;
    height: 100%;
    left: 0;
    top: 0;
  }

  &__badge {
    position: absolute;
    left: 15px;
    top: 10px;
    height: 27px;
    width: 27px;
    background: #2b7bfe;
    border-radius: 50%;
    color: #fff;
    z-index: 3;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  &__info {
    position: absolute;
    bottom: 15px;
    left: 15px;
    z-index: 3;
    color: #ffffff;

    span {
      display: block;
    }

    .info-title {
      font-size: 13px;
      font-weight: 500;
    }

    .info-seller {
      padding: 5px 0 0;
      font-size: 11px;
      color: rgba(#ffffff, 0.75);

      i {
        color: #fff;
      }
    }
  }
}
</style>
