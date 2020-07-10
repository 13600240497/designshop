<template>
    <el-dialog title="首页访问地址" :visible.sync="oViewAccessLinks.dialogLinksVisible">
        <el-row>
            <el-button v-for="item in oViewAccessLinks.pageLinks" type="primary" :key="item.lang" @click="redirect(item.page_url)">{{ item.lang_name }}</el-button>
            <p>{{ oViewAccessLinks.tips }}
                <el-button @click="redistribution()" v-if="oViewAccessLinks.tips" type="primary">重新发布</el-button>
            </p>
        </el-row>
    </el-dialog>
</template>

<script>
    import { homeReleased } from '../../plugin/api'
    export default {
        name: 'homeViewAccessLinks',
        props: {
            oViewAccessLinks: {
                type: Object
            }
        },
        data () {
            return {}
        },
        methods: {
            redirect (url) {
                window.open(url)
            },

            // 重新发布
            async redistribution () {
                let params = {
                    page_id: this.oViewAccessLinks.urlID
                }

                await homeReleased(params)
                this.oViewAccessLinks.dialogLinksVisible = false
            }
        }
    }
</script>

<style scoped>

</style>