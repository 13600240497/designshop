<template>
    <div class="geshop-loading-goodsList">
        <ul>
            <li><span></span><p></p><p></p></li>
            <li><span></span><p></p><p></p></li>
            <li><span></span><p></p><p></p></li>
            <li><span></span><p></p><p></p></li>
        </ul>
    </div>
</template>

<style lang="less">
.geshop-loading-goodsList {
    padding: 0.32rem;
    padding-top: 0px; 
    ul {
        padding: 0.32rem 0.32rem 0rem;
        background: #fff;
        border-radius: 8px;
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        background: #fff;
    }
    li {
        width: 4.24rem;
        padding-bottom: 40px;
        span {
            display: block;
            width: 100%;
            height: 5.65333333rem;
            background: #F2F2F2;
        }
        p {
            display: block;
            width: 90%;
            margin-top: 10px;
            height: 12px;
            background: #f2f2f2;
            &:last-child {
                display: block;
                width: 70%;
                margin-top: 10px;
                height: 12px;
                background: #f2f2f2;
            }
        }
    }
}
</style>