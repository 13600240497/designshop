<template>
    <div class="geshop-components-soldout" v-if="visible">
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
    }
};
</script>

<style lang="less" scoped>
    .geshop-components-soldout {
        position: absolute;
        left: 0px;
        right: 0px;
        top: 0px;
        bottom: 0px;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.2);

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
            font-size: 16px;
            line-height: 1.1em;
            color: #fff;
            padding: 0 20px;
        }
    }
</style>
