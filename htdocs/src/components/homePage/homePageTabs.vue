<template>
    <div :class="prfClass">
        <el-tabs v-model="oCommonData.homeTabName" type="card" @tab-click="handleHomeTabClick" class="tab-header">
            <el-tab-pane v-for="(item,key) in oCommonData.places" :label="item.platform_name" :name="key" :key="key"></el-tab-pane>
        </el-tabs>
    </div>
</template>

<script>
    export default {
        name: 'homePageTabs',
        props: {
            oCommonData: {
                type: Object
            },
            homeTabClick: {
                type: Function
            }
        },
        data () {
            return {
                prfClass: 'home-tab'
            }
        },
        methods: {
            handleHomeTabClick (event) {
                this.oCommonData.homeTabName = event.name
                this.homeTabClick();
            }
        }
    }
</script>

<style scoped>

</style>