<template>
    <div :class="['cartItemMessage', 'cartItemMessage____'+position]">
        <div class="cartItemMessage__body">
            <div class="cartItemMessage__bodyContent">{{text}}</div>
            <div class="cartItemMessage__bodyDetails">
                <span class="cartItemMessage__bodyDetailsTime">{{time}}</span>
                <span class="cartItemMessage__bodyDetailsRole">{{author}}</span>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        components: {},
        props: {
            position: {
                validator: function (value) {
                    return ['left', 'right'].indexOf(value) !== -1
                }
            },
            text: String,
            time: String,
            author: String,
        }
    };
</script>

<style lang="scss">
    .cartItemMessage {
        display: flex;

        &____left {
            justify-content: flex-start;

            .cartItemMessage__bodyContent {
                background: rgba(#4CD09E, 0.15);
                border-radius: 12px 12px 12px 0;
            }

            .cartItemMessage__bodyDetails {
                text-align: left;
                display: flex;
                flex-direction: row-reverse;
                align-items: center;
                justify-content: flex-end;


                .cartItemMessage__bodyDetailsRole {
                    color: #4CD09E;
                    padding-left: 0;
                }

                .cartItemMessage__bodyDetailsTime {
                    padding-left: 20px;
                }
            }
        }

        &____right {
            justify-content: flex-end;
        }

        &__body {
            &:not(:first-of-type) {
                margin-top: 15px;
            }
        }

        &__bodyContent {
            padding: 15px 20px;
            background: rgba(#A8A8A8, 0.15);
            border-radius: 12px 12px 0 12px;
            white-space: pre-wrap;
        }

        &__bodyDetails {
            margin-bottom: 10px;
            text-align: right;
        }

        &__bodyDetailsTime {
            color: #CCCCCC;
            font-size: 12px;
        }

        &__bodyDetailsRole {
            padding-left: 20px;
            font-size: 13px;
            color: #A8A8A8;
            font-weight: 500;
        }
    }
</style>
