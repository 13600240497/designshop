<template>
    <div class="geshop-components-default-soldout" v-if="visible" :class="site">
        <table>
            <tr>
                <td>
                    <div class="geshop-soldout-wrapper">
                        <span class="geshop-soldout-table">
                            <label>{{ label }}</label>
                        </span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</template>

<script>
    export default {
        name: 'geshop-soldout',
        props: ['visible'],
        data() {
          return {
              site: GESHOP_SITECODE.split('-')[0]
          }
        },
        computed: {
            label() {
                return window.GESHOP_LANGUAGES['btn_sold_out']
            }
        }
    }
</script>

<style lang="less" scoped>
    .geshop-components-default-soldout {
        position: absolute;
        left: 0px;
        right: 0px;
        top: 0px;
        bottom: 0px;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.2);
        display: none;
        .geshop-soldout-wrapper {
            display: inline-block;
            width: 160px;
            height: 160px;
            border-radius: 100%;
            background: rgba(0, 0, 0, .4);
            overflow: hidden;
        }
        .geshop-soldout-table {
            display: table;
            width: 100%;
            height: 100%;
        }
        table {
            width: 100%;
            height: 100%;
        }
        td {
            text-align: center;
            vertical-align: middle;
        }
        label {
            display: table-cell;
            text-align: center;
            vertical-align: middle;
            font-size: 24px;
            line-height: 1.1em;
            color: #fff;
            padding: 0 20px;
        }
    }

</style>
