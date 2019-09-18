<template>
    <div>
        <h1 class="title">結果</h1>
        <router-link to="/study">戻る
        </router-link>
        <ul v-if="errors.length">
            <li v-for="error in errors" class="is-danger">エラー：{{ error }}</li>
        </ul>
        <p>列をクリックすると学習状況を切り替えられます。</p>
        <v-client-table v-if="results.length" :data="results" :columns="visibleColumns" :options="options" @row-click.self="toggleComplete">
            <template :slot="slotHeaderName">
                <label for="meaning">意味 </label>
                <div class="select">
                    <select v-model="language" id="meaning">
                        <option value="eng">英語</option>
                        <option value="jpn">日本語</option>
                    </select>
                </div>
            </template>
            <!-- checkbox for each row-->
            <template slot="isCorrect" slot-scope="props">
                {{ props.row.isCorrect | isCorrect }}
            </template>
            <template :slot="slotName" slot-scope="props">
                <ul>
                    <li v-for="def in props.row[slotName]">{{ def }}</li>
                </ul>
            </template>
            <template slot="level" slot-scope="props">
                {{ Math.floor(props.row.level / 10) + 1 }}
            </template>
            <template slot="isComplete" slot-scope="props">
                {{ props.row.isComplete | isComplete }}
            </template>
        </v-client-table>
    </div>
</template>
<style>
.VuePagination__count {
    display: none !important;
}

</style>
<script>
import { mixin } from '../mixin';
export default {
    mixins: [mixin],
    computed: {
        slotName() {
            if (this.language === 'eng') {
                return 'meaning';
            }
            return 'meaning_jp';
        },
        slotHeaderName() {
            if (this.language === 'eng') {
                return 'h__meaning';
            }
            return 'h__meaning_jp';
        },
        visibleColumns() {
            if (this.language === 'eng') {
                return ['isCorrect', 'lemma', 'level', 'meaning', 'isComplete'];
            }
            return ['isCorrect', 'lemma', 'level', 'meaning_jp', 'isComplete'];
        }
    },
    data() {
        return {
            language: 'eng',
            results: [],
            options: {
                sortable: ['isCorrect', 'lemma', 'level', 'isComplete'],
                filterable: false,
                headings: {
                    isCorrect: '正解',
                    lemma: '単語',
                    level: 'レベル',
                    isComplete: '学習状況'
                },
                rowClassCallback(row) {
                    return row.isComplete ? 'has-background-warning' : '';
                },
            },
        }
    },
    filters: {
        isCorrect(value) {
            if (value) {
                return '○';
            }
            return '☓';
        },
        isComplete(value) {
            if (value) {
                return '済';
            }
            return '未';
        }
    },
    methods: {
        isComplete(task) {
            if (task.is_complete) {
                return { 'has-background-warning': true };
            }
        },
        toggleComplete(e) {
            let found = this.results.find(task => task.id == e.row.id);
            let index = this.results.findIndex(task => task.id == e.row.id);
            if (!found) {
                return;
            }
            found.isComplete = !found.isComplete;
            this.patch('/api/tasks/', {
                words: [found.id],
                isComplete: found.isComplete
            });
            console.log(index);
            this.results[index] = found;
            this.$ls.set('results', this.results, lsExpiryTime);
        },
    },
    mounted() {
        this.results = this.$ls.get('results');
    }
}

</script>
