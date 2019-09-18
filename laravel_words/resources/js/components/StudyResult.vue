<template>
    <div>
        <h1 class="title">結果</h1>
        <router-link to="/study">戻る
        </router-link>
        <!-- <p>同じ条件で再度復習する</p>
 -->
        <ul v-if="errors.length">
            <li v-for="error in errors" class="is-danger">エラー：{{ error }}</li>
        </ul>
        <v-client-table v-if="results.length" :data="results" :columns="columns" :options="options" @row-click.self="toggleComplete">
            <!-- checkbox for each row-->
            <template slot="isCorrect" slot-scope="props">
                {{ props.row.isCorrect | isCorrect }}
            </template>
            <template slot="meaning" slot-scope="props">
                <ul>
                    <li v-for="def in props.row.meaning">{{ def }}</li>
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
    data() {
        return {
            results: [],
            columns: ['isCorrect', 'lemma', 'level', 'meaning', 'meaning_jp', 'isComplete'],
            options: {
                sortable: ['isCorrect', 'lemma', 'level', 'isComplete'],
                filterable: false,
                headings: {
                    isCorrect: '正解',
                    lemma: '単語',
                    level: 'レベル',
                    meaning: '意味',
                    meaning_jp: '意味（日本語）',
                    isComplete: '学習状況（クリックで切り替え）'
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
