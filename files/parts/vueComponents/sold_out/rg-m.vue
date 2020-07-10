<template>
    <div class="geshop-components-soldout-bottom" v-if="visible">
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
    props: {
        visible: {
            type: Boolean,
            default: false
        },
        label: {
            type: String,
            default: ''
        }
    },
    data () {
        return {};
    }
};
</script>

<style lang="less" scoped>
    .geshop-components-soldout-bottom {
        position: absolute;
        left: 0px;
        right: 0px;
        bottom: 0px;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        z-index: 10;

        .geshop-soldout-wrapper {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 88/75rem;
            display: inline-block;
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
            font-size: 32/75rem;
            line-height: 1.1em;
            color: #fff;
            padding: 0 20/75rem;
        }
    }
</style>
