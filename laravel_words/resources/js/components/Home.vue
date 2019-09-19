<template>
    <div>
        <p v-if="!name">現在試験運用中です。id: guest, pass: guestでゲストとしてログインできます。</p>
        <p v-if="name">ようこそ！ {{ name }}さん</p>
        <p v-if="name && currentVocabulary">あなたの現在の英単語力は{{ currentVocabulary }}語です。</p>
    </div>
</template>
<script>
import { mixin } from '../mixin';
export default {
    mixins: [mixin],
    conputed: {
        currentVocabulary() {
            if (this.$ls.get('level')) {
                return this.$ls.get('level');
            }
            return this.level * 100;
        }
    },
    data() {
        return {
            projects: [],
            level: 0,
            name: '',
        }
    },
    mounted() {
        if (Laravel.apiToken) {
            this.refreshProjects();
        }
        if (Laravel.apiToken && !this.$ls.get('name')) {
            axios.get('/api/users')
                .then(response => {
                    this.name = response.data.name;
                    this.$ls.set('name', this.name, lsExpiryTime);
                });
            return;
        }
        this.name = this.$ls.get('name');
    }
}

</script>
