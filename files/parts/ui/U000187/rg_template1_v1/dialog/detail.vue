<template>
    <div class="inner">
        <template v-if="lang === 'fr'">
            <h2 class="site-font-bold">Détails</h2>
            <p class="p1 mt10 site-font-bold">1. Règles du tirage au sort :</p>
            <p class="p2"> a. Deux façons de jouer : <br>
                - Chaque nouveau membre gagne 2 chances gratuites chaque jour<br>
                - Vous pouvez également partager le lien pour gagner une autre chance de jeu. Pour tous, plus vous
                partagez, plus vous avez de chances de gagner. SANS LIMITE ! <br>
                * Votre ami invité DOIT cliquer sur votre lien partagé et s'inscrire. Les amis recevront également
                des chances gratuites. C'est un gagnant-gagnant !<br>
                b. Les coupons ne sont valables que pour 1 jour, tous les coupons sont automatiquement émis sur le
                compte.<br>
                c. Les coupons ne peuvent pas être utilisés avec d'autres codes promotionnels, rabais ou
                coupons.<br>
                d. Pour ceux qui utilisent des moyens illégaux pour obtenir des coupons à plusieurs reprises,
                Rosegal reste le droit de déclarer les coupons comme nuls.<br>
            </p>
            <p class="p1 mt10 site-font-bold">2. PRIX DE NOUVEAU UTILISATEUR</p>
            <p class="p2"> Le prix des nouveaux utilisateurs ne sera utilisé que pour certains produits de MEILLEUR
                PRIX SEULEMENT POUR VOUS. Seuls ceux qui n'ont jamais acheté de produit sur Rosegal auparavant (y
                compris PC, M &amp; APP) peuvent profiter de l'offre.
            </p>
        </template>
        <template v-else>
            <h2 class="site-font-bold">Details</h2>
            <p class="p1 mt10 site-font-bold">1. Lucky Draw Rules:</p>
            <p class="p2"> a. Two Ways To Play: <br>
                - Gain 2 Free chances every day (for new member) <br>
                - Share to earn another chance to play for every signup (for all members), no limits! <br>
                * Your invited friends MUST click your shared link and sign up. Friends will also receive FREE Chances.
                It's
                a win-win! <br>
                b. The coupons only valid for 1 day, check your coupons in your account. <br>
                c. Coupons cannot be used in conjunction with other promotional, discount, or coupon code. <br>
                d. For those who use illegal means to get coupons for multiple times, Rosegal preserves the right to
                declare
                the coupons as invalid. <br>
            </p>
            <p class="p1 mt10 site-font-bold">2. Prizes:</p>
            <p class="p2"> - A free Product<br>
                - Free Shipping Coupon<br>
                - Free Shipping Over $39 Coupon<br>
                - Lucky Bags<br>
                - $9 Off $59<br>
                - $20 Off $30<br>
            </p>
            <p class="p1 mt10 site-font-bold">3.New Users' Price</p>
            <p class="p2">New users' price are only available for certain BEST PRICE ONLY FOR YOU products. Only those
                who
                had never purchased product(s) on Rosegal before (including PC, M &amp; APP) could enjoy this offer.
            </p>
        </template>
    </div>
</template>

<script>
export default {
    name: 'detail',
    data () {
        return {
            lang: window.GESHOP_LANG
        };
    },
    components: {},
    methods: {}
};
</script>

<style scoped lang="less">
    .inner {
        width: 570px;
        height: auto;
        background: rgba(255, 255, 255, 1);
        border-radius: 2px;
        text-align: left;
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        box-sizing: border-box;
        padding: 50px 64px 58px;
        color: #222222;

        .mt10 {
            margin: 12px 0;
        }

        h2 {
            margin: 0 auto 32px;
            text-align: center;
            height: 33px;
            font-size: 28px;
            color: rgba(245, 53, 101, 1);
            line-height: 33px;
            text-transform: uppercase;
        }

        .p1 {
            font-size: 14px;

        }

        .p2 {
            font-size: 14px;
            color: #333;
            line-height: 17px
        }
    }

</style>
