<template>
    <site-layout @publicReady="publicReady" :footLink="1">
        <el-row :span="24" class="geshop-Activity-tit">
            <span class="geshop-Activity-title">系统>>清除缓存</span>
        </el-row>
        <!-- 合并链接表单 -->
        <el-row class="client-box deeplink-client-box">
            
           
            <el-col :span="24">
                <el-form :inline="true" :model="cacheForm" :rules="cacheFormRules" ref="cacheFormWrap">
                    <el-form-item prop="url">
                        <el-input v-model="cacheForm.url" placeholder="请输入完整URL" class="cache-url-box"></el-input>
                        
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" :loading="ajaxLoading" @click="onSubmit('cacheFormWrap')">
                            <template v-if="!ajaxLoading">
                                立即清除缓存
                            </template>
                            <template v-else>
                                缓存清除中
                            </template>
                        </el-button>
                         <el-button @click="resetForm('cacheFormWrap')">重置</el-button>
                    </el-form-item>
                </el-form>
            </el-col>
             <el-col :span="24"  > 
                <el-alert
                    title="请输入完成的URL"
                    description="例如：https://m.zaful.com/promotion/virus-protection-5558.html?is_app=1"
                    type="info"
                    show-icon
                    :closable="false"
                    />
            </el-col>
        </el-row>
       
    </site-layout>
</template>

<script>
import siteLayout from './layouts/Layout.vue';
import bus from '../store/bus-index.js';
import { clear_url_cache,soa_rule_list } from '../plugin/api';

export default {
    name: "DeepLink.vue",
    components: { siteLayout },
    data () {
        return {
            siteInfo: {}, // 站点信息
            sitePlat: '',
            places: '', // 站点端信息
            cacheForm:{
                url:''
            },
            cacheFormRules:{
                url:[{ required: true, message: '请输入页面完整的url', trigger: 'blur',type: 'url'}],
            },
            ajaxLoading:false
        };
    },
    mounted () {
    },
    methods: {
        siteChange (val) {
            this.appForm.action_list = this.appForm.site_list[val].action;
        },
        publicReady () {
           
            this.permissions = JSON.parse(localStorage.getItem('actionPermissions')).data;

            bus.$on('giveData', data => {
                this.siteInfo = data;
                this.sitePlat = this.siteInfo.site.split('-')[1];
            });
            // 设置当前站点信息
            this.places = JSON.parse(localStorage.currentSites).sites;
        },
       onSubmit(formName){

             
            // await clear_url_cache()
            this.$refs[formName].validate(async (valid) => {
                if (valid) {
                    try {
                        this.ajaxLoading = true;
                        let clearResultData =  await clear_url_cache({url:this.cacheForm.url});
                        this.ajaxLoading = false;
                        if(clearResultData.code == 0){
                            this.$message({
                                message: '缓存清除成功！',
                                showClose: true,
                                type: 'success'
                            });
                        }else{
                            this.$message({
                                message: '清除页面缓存失败，请重新尝试',
                                showClose: true,
                                type: 'error'
                            });
                        }
                        console.log(clearResultData);
                    } catch (error) {
                        console.log(error)
                    }
                
                } else {
                    
                    return false;
                }
            });
        },
        resetForm(formName){
            this.$refs[formName].resetFields();
        }
    }
};
</script>

<style lang="less" scoped>
     .deeplink-client-box {
        display: flex;
        flex-direction:column;
        justify-content: flex-start;
        align-content: center;
        min-width: 880px;
        min-height: calc(100vh - 300px);
        background-color: #FFFFFF;
        padding: 80px;
        .cache-url-box{
            width: 600px;
        }
        
    }
</style>
