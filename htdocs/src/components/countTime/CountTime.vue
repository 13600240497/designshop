<template>
    <span>
        <span class="date" v-if="d>0">{{d}}天</span>
        <span  v-if="d>0">:</span>
        <span class="date"  v-if="h>0">{{h}}小时</span>
        <span v-if="h>0">:</span>
        <span class="date" v-if="m>0">{{m}}分</span>
        <span  v-if="m>0">:</span>
        <span class="date">{{s>9?s:'0'+s}}秒</span>
    </span>
</template>
<script>
export default {
  props:['pageClearTime','time-lear-all'],
 
    data(){
      return {
        d: 0,
        h: 0,
        m: 0,
        s: 0,
        timeNum:0,
        clearFun:null
      }
    },
  
  
//    watch: {
//        pageClearTime:function(val){
//             this.timeNum = val;
//             clearTimeout(this.clearFun);
//             this.countTime();
//        }
//    },
   mounted(){
      
       this.timeNum = this.pageClearTime;
        clearTimeout(this.clearFun);
        this.countTime();
      
   },
  methods: {
      countTime: function() {
       
        let leftTime  = this.timeNum;
        //定义变量 d,h,m,s保存倒计时的时间
        if (leftTime >= 0) {
            this.d = Math.floor(leftTime/1000 / 60 / 60 / 24);
            this.h = Math.floor((leftTime /1000 / 60 / 60) % 24);
            this.m = Math.floor((leftTime /1000 / 60) % 60);
            this.s = Math.floor((leftTime/1000) % 60);

            //递归每秒调用countTime方法，显示动态时间效果
            this.clearFun = setTimeout(()=>{
                this.timeNum = this.timeNum - 1000;
                
                this.countTime();
            }, 1000)
            
        }else{
             this.$emit('time-lear-all');
            clearTimeout(this.clearFun);
        }
        
        
    },
  }
}
</script>