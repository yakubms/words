<template>
    <div>
        <p>ホーム</p>
        <p v-if="level">あなたの現在の英単語力：{{ level * 100 }}語</p>
    </div>
</template>
<script>
import { mixin } from '../mixin';
export default {
    mixins: [mixin],
    data() {
        return {
            level: ''
        }
    },
    mounted() {
        this.refreshProjects();
        this.level = this.$ls.get('level');
    }
}

</script>
