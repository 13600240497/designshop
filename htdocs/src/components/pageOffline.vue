<template>
    <site-layout @publicReady="publicReady" :footLink="1">
        <el-row :span="24" class="geshop-Activity-tit">
            <span class="geshop-Activity-title">系统>>页面数据清理</span>
        </el-row>
        <!-- 合并链接表单 -->
        <el-row class="client-box" v-loading="pageLoading">
            <el-form :model="appForm" label-width="100px">
                
                
               <!-- <el-form-item  label="站点选择："  v-if="appFormData.siteLabel.length">
                    <el-radio-group  v-model="appForm.site">
                        <el-radio :label="item.value"  v-for="item in appFormData.siteLabel" :key="item.value">{{item.label}}</el-radio>
                        
                    </el-radio-group>
                </el-form-item>  -->

                <el-form-item  label="页面范围：" v-if="appFormData.pageTimeLabel.length" >
                    <el-radio-group v-model="appForm.pageTime" @change="changePageTime">
                        <el-radio :label="item.value"  v-for="item in appFormData.pageTimeLabel" :key="item.value">{{item.label}}</el-radio>
                       
                    </el-radio-group>
                </el-form-item>

                <el-form-item label="平台选择：" v-if="appFormData.portLabel.length">
                    <el-checkbox-group v-model="appForm.port" @change="changePortLabel">
                        <el-checkbox :label="item.code" v-for="item in appFormData.portLabel" :key="item.code"  >{{item.name}}</el-checkbox>
                    </el-checkbox-group>
                </el-form-item>

                <el-form-item label="渠道选择：" v-if="appFormData.pipleLabel.length">
                    <el-checkbox v-model="appForm.pipleAll"  @change="handleCheckAllChange">全选</el-checkbox>
                    <el-checkbox-group v-model="appForm.piple" @change="handleCheckedItemChange">
                        <el-checkbox :label="item.code" v-for="item in appFormData.pipleLabel" :key="item.code" >{{item.name}}</el-checkbox>
                       
                        
                    </el-checkbox-group>
                </el-form-item>

                <el-form-item label="时长预估：" v-if="appForm.pageNum > 0">
                    <div  class="page_offline_text">待清理页面数{{appForm.pageNum}}个</div>
                    <!-- <div  class="page_offline_text">预计等待时长{{clearPageTime}}分钟</div> -->
                </el-form-item>

                <el-form-item >
                   <el-button class="page_offline_action_button" :disabled="submitDisable" :loading="pageClearIng" @click="sendPageClear" type="primary">
                       
                        <template v-if="pageClearIng">页面清理中</template>
                        <template v-else>立即清理</template>
                   </el-button>
                   
                   <div>
                       
                   </div>
                </el-form-item>

            </el-form>
        </el-row>

        <!-- <el-dialog
            title="页面清理中"
            :visible.sync="dialogVisible"
            width="30%"
            :close-on-press-escape = "false"
            :close-on-click-modal = "false"
           
            >
            <div v-if="dialogVisible" class="pop_time_box">
                页面正在清理中，预计还需要 <count-time :page-clear-time="clearPageTime*60*1000" class="time_count" @time-lear-all="timeClearCall"></count-time> 完成


            </div>
           
        </el-dialog> -->

    </site-layout>

    

</template>

<script>
import siteLayout from './layouts/Layout.vue';
import bus from '../store/bus-index.js';
import {ZF_getCountrySiteList,get_clear_page,send_clear_page} from '../plugin/api';

import CountTime from './countTime/CountTime.vue';

export default {
    name: "DeepLink.vue",
    components: { siteLayout,CountTime },
    data () {
        return {
            siteInfo: {}, // 站点信息
            sitePlat: '',
            places: '', // 站点端信息
            // submitLoading: false,
            pageLoading:true,//页面数据加载中
            pageData:null,
          
            dialogVisible:false,//清除中，弹出框
            pageClearIng:false,//按钮清理中
            appFormData:{
                pageTimeLabel:[
                    {
                        label:'一年后下线页面',
                        value:'1' 
                    },{
                        label:'六个月后下线页面',
                        value:'2'
                    
                    },{
                        label:'三个月后下线页面',
                        value:'3'
                    
                    }
                ],
                // siteLabel:[
                //     {
                //         label:'ZAFUL',
                //         value:'1' 
                //     },
                //     {
                //         label:'RoseGal',
                //         value:'2' 
                //     },
                //     {
                //         label:'Dresslily',
                //         value:'3' 
                //     },
                //     {
                //         label:'GearBest',
                //         value:'4' 
                //     }

                // ],
                portLabel:[//平台数据
                    // {
                    //     name:'PC',
                    //     code:'1' 
                    // }
                    
                ],
                pipleLabel:[//渠道数据
                    // {
                    //     name:'全球站',
                    //     code:'1' 
                    // }
                    
                ]
            },
            appForm: {
               pageTime:'',
        
               piple:[],
               port:[],
               pipleAll:0,
               pageNum:0,
            } // app表单
            
        };
    },
    computed: {
        /**
         * 按钮是否可以点击
         */
        submitDisable:function(){
            let appForm = this.appForm;
            
            if(!!appForm.pageTime||appForm.piple.length>0||appForm.port.length > 0){
                return false;
            }else{
                return true;
            }
        },
        // /**
        //  * 计算页面清理预估时间
        //  */
        // clearPageTime:function () { 
        //     let clearPageTimeNum = this.appForm.pageNum*5/60;
        //     if (clearPageTimeNum >=1){
        //         return Math.ceil(clearPageTimeNum)
        //     }else{
        //         return clearPageTimeNum.toFixed(2);
        //     }
          
        //  }
    },
    mounted () {
       //获取页面数据
       ZF_getCountrySiteList({activity_type:1}).then(res=>{
           this.pageLoading = false;//移除loading图标
         
           if(res.code == 0){//如果请求成功保持请求数据，并复制平台数据
              this.pageData = Object.assign({},res.data);

              this.pageData.all_platforms.length > 0 &&  (this.appFormData.portLabel=[...this.pageData.all_platforms]);
               
           }
        //     //页面进入后，先检查上一次下线任务是否完成
           
        //     getPageOfflineTotal().then(res=>{
               
        //         this.appForm.pageNum = res.data['page-num'];
        //        //如果上一次任务还未完成
        //        if(this.appForm.pageNum > 0){
        //            this.pageClearIng = true;
        //            this.dialogVisible =true;
        //        }
        //    });
           
       })
    },
    methods: {
        /**
         * 选择页面创建时间范围，获取页面数据
         */
        changePageTime(){
            //获取下线页面数量
            this.getPageNum({page_create_time:this.appForm.pageTime,platform:this.appForm.port.join('-'),pipeline:this.appForm.piple.join('-')});
        },
        /**
         * 选择一个平台
         */
        changePortLabel(){
            let portList = this.appForm.port;
            let portListArr =  [];
            let hash = {};//用于判断portListArr去重使用
           
            if(portList.length>0){//如果选择了平台，遍历平台并获取平台的渠道数据
                portList.map((v,i)=>{
                    let item = this.pageData.support_pipelines[v];
                    Object.keys(item).map((val,ind)=>{
                        var newItem = {code:item[val].code,name:item[val].name};
                        
                        if(!hash[item[val].code]){//如果该渠道数据未写入portListArr容器中
                            hash[item[val].code] = true;
                            portListArr.push(newItem);
                        }
                        // if(this.appForm.piple.includes(item[val].code)){
                        //     let piple =  this.appForm.piple;
                        //     piple.splice(piple.findIndex(i =>i.code == item[val].code ),1);
                        // }
                    });
                   
                   
                    //赋值给页面渠道数据
                    this.appFormData.pipleLabel = [...portListArr];
                    
                   
                });

                //平台变化可能会让渠道减少，减少的渠道要取消选中状态
                let piple = this.appForm.piple;
                this.appForm.piple.map((v,i)=>{
                    if(!hash[v]){
                        piple.splice(i,1);
                    }
                });
            }else{//如果没有选择平台，要清空渠道数据
                portListArr=[];
                hash = {};
                this.appFormData.pipleLabel =[];
                this.appForm.piple=[];
            }
            //从新计算渠道的全选状态
            this.handleCheckedItemChange([...this.appForm.piple]);
            //获取下线页面数量
           this.getPageNum({page_creat_time:this.appForm.pageTime,port:this.appForm.port.join('-'),piple:this.appForm.piple.join('-')});

        },
        /**
         * 全选功能
         * 
         */
        handleCheckAllChange(){//是否全选
           
            if(this.appForm.pipleAll ){
                let piplabelCode = [];
                // let allPiplabelCode;

                //把渠道的值全部查出来
                this.appFormData.pipleLabel.map((item,i)=>{
                    piplabelCode.push(item.code);
                });
                this.appForm.piple =[...piplabelCode]; 
            }else{
                this.appForm.piple=[];
            }
            //获取下线页面数量
            this.getPageNum({page_create_time:this.appForm.pageTime,platform:this.appForm.port.join('-'),pipeline:this.appForm.piple.join('-')});

        
        },
         /**
         * 单个渠道选择和全选按钮联动
         * @param{Array} 是一个渠道组合成的数组
         */
        handleCheckedItemChange(val){
    
            let piplabelCode = [];
           
            this.appFormData.pipleLabel.map((item,i)=>{
                piplabelCode.push(item.code);
            });
            
            this.appForm.pipleAll  = val.sort().toString() === piplabelCode.sort().toString();
          
            //获取下线页面数量
            this.getPageNum({page_create_time:this.appForm.pageTime,platform:this.appForm.port.join('-'),pipeline:this.appForm.piple.join('-')});
        },
        /**
         * 获取页面数量
         * @param{Number} page_creat_time,最近更新时间，取值1，2，3，1:一年后下线页面，2:六个月后下线页面，3:三个月后下线页面
         * @param{String} platform，平台， app ，wap， app,可以传多值用“-”链接，例如：app-wap
         * @param{String} pipeline，渠道简写，DL，DLFR ...可以传多值用“-”链接，例如：DL-DLFR
         */
        getPageNum(params){
            
            this.pageLoading=true;
           return get_clear_page(params).then(res=>{ 
                this.pageLoading = false;
                if(res.code == 0){
                    this.appForm.pageNum = res.data['page-num'];
                }
                return res;
            })
        },
        /**
         * 发送请求到后端，请求页面下线
         * void
         */
        sendPageClear(){
            this.$confirm('此操作将让活动页面下线, 是否继续?', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                //点击确定
                if(!this.pageClearIng){
                   
                    this.pageLoading=true;//页面数据加载中
                    this.pageClearIng = true;//按钮loading中

                    //执行下线页面请求
                    send_clear_page({page_create_time:this.appForm.pageTime,platform:this.appForm.port.join('-'),pipeline:this.appForm.piple.join('-'),offline:1})
                    .then(res=>{
                        this.pageLoading = false;
                        this.pageClearIng = false;
                        
                        if(res.code == 0){
                            this.$message({
                                message: '页面下线成功',
                                type: 'success'
                            });
                            this.appForm.pageNum = 0;
                            // this.dialogVisible = true;//弹出框提醒用户
                        }
                    })
                }
            }).catch(() => {
                //点击取消
                return false;        
            });


        },
        /**
         * 倒计时到了，选择数据进行清空
         */
        timeClearCall(){
        
            this.pageClearIng = false;
            this.dialogVisible = false;
            this.appForm = Object.assign({},{pageTime:'',piple:[],port:[],pipleAll:0, pageNum:0});
        
        },
        // /**
        //  * 
        //  */
        // siteChange (val) {
        //     this.appForm.action_list = this.appForm.site_list[val].action;
        // },
        publicReady () {
       
            this.permissions = JSON.parse(localStorage.getItem('actionPermissions')).data;

            bus.$on('giveData', data => {
                this.siteInfo = data;
                this.sitePlat = this.siteInfo.site.split('-')[1];
            });
            // 设置当前站点信息
            this.places = JSON.parse(localStorage.currentSites).sites;
        },
         
        // formItemChange(){
        //     console.log(this.appForm)
        // }
        
    }
};
</script>

<style lang="less" scoped>
   .client-box{
       background: #fff;
       padding: 30px;
   }
    .page_offline_action_button{
        width: 200px;
    }
    .page_offline_text{
        color:#606266;
    }
    .pop_time_box{
        font-size: 18px;
    }
    .time_count{font-weight: bold;}
</style>

