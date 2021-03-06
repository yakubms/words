<template>
    <div>
        <h1 v-if="!isLoading" class="title">単語帳を編集する</h1>
        <router-link v-if="!isLoading" to="/words">戻る
        </router-link>
        <p v-if="isLoading">読み込み中……</p>
        <div v-if="errors.length" class="notification is-danger">{{ errors }}</div>
        <form v-if="taskLength" @submit.prevent="onExport">
            <div class="field">
                <div class="control">
                    <label for="export">単語帳のエクスポート</label>
                    <div class="select">
                        <select id="export" v-model="exportOption">
                            <option value="all">全て</option>
                            <option value="ongoing">学習中の単語のみ</option>
                            <option value="complete">学習済みの単語のみ</option>
                        </select>
                    </div>
                </div>
                <div class="control">
                    <button type="submit" class="button is-info">この単語帳をエクスポートする</button>
                </div>
            </div>
        </form>
        <form v-if="taskLength" @submit.prevent="onComplete">
            <div class="field">
                <div class="control">
                    <button type="submit" class="button is-success" :disabled="!checkedWords.length">チェックした単語の学習状況を変更する</button>
                </div>
            </div>
        </form>
        <form v-if="taskLength" @submit.prevent="onDelete">
            <v-client-table :data="tasks" :columns="visibleColumns" :options="options" @row-click.self="toggleComplete">
                <!-- checkbox for each header (prefix column name with h__-->
                <template slot="h__no">
                    <label for="checkAll">
                        <input type='checkbox' id='checkAll' @click='toggleCheckAll' :checked="checkAll">全て選択</label>
                </template>
                <!-- checkbox for each row-->
                <template slot="no" slot-scope="props">
                    <div class="checkbox" @click.capture.self.stop="toggleCheck(props.row.id)">
                        <label :for="props.row.id" @click.stop>
                            <input type='checkbox' :id="props.row.id" v-model="checkedWords" :value="props.row.id">{{ props.row.no }}
                        </label>
                    </div>
                </template>
                <template :slot="slotHeaderName">
                    <label for="meaning">意味 </label>
                    <div class="select">
                        <select v-model="language" id="meaning">
                            <option value="eng">英語</option>
                            <option value="jpn">日本語</option>
                        </select>
                    </div>
                </template>
                <template :slot="slotName" slot-scope="props">
                    <ul>
                        <li v-for="def in props.row[slotName]">{{ def }}</li>
                    </ul>
                </template>
                <template slot="level" slot-scope="props">
                    {{ Math.floor(props.row.level / 10) + 1 }}
                </template>
                <template slot="is_complete" slot-scope="props">
                    {{ props.row.is_complete | isComplete }}
                </template>
            </v-client-table>
            <div class="field">
                <div class="control">
                    <button type="submit" class="button is-danger" :disabled="!checkedWords.length">チェックした単語を削除</button>
                </div>
            </div>
        </form>
    </div>
</template>
<style>
td.checkbox_parent {
    overflow: hidden;
    position: relative;
}

div.checkbox {
    position: absolute !important;
    width: 100%;
    height: 100%;
    left: 0;
    top: 0;
}

</style>
<script>
import swal from 'sweetalert';
import { mixin } from '../mixin';

export default {
    mixins: [mixin],
    props: {
        id: String
    },
    computed: {
        taskLength() {
            if (!this.tasks) {
                return null;
            }
            return this.tasks.length;
        },
        slotName() {
            if (this.language === 'eng') {
                return 'defs_en';
            }
            return 'defs_jp';
        },
        slotHeaderName() {
            if (this.language === 'eng') {
                return 'h__defs_en';
            }
            return 'h__defs_jp';
        },
        visibleColumns() {
            if (this.language === 'eng') {
                return ['no', 'lemma', 'level', 'defs_en', 'is_complete', 'created_at'];
            }
            return ['no', 'lemma', 'level', 'defs_jp', 'is_complete', 'created_at'];
        }
    },
    data() {
        return {
            language: 'eng',
            book: '',
            exportOption: 'all',
            tasks: '',
            checkAll: false,
            checkedWords: [],
            options: {
                sortable: ['no', 'lemma', 'level', 'is_complete', 'created_at'],
                headings: {
                    lemma: '単語',
                    level: 'レベル',
                    defs_en: '意味',
                    is_complete: '学習状況',
                    created_at: '登録日'
                },
                texts: {
                    filterPlaceholder: '検索する'
                },
                perPage: 50,
                perPageValues: [10, 25, 50, 100, 500],
                rowClassCallback(row) {
                    return row.is_complete ? 'has-background-warning' : '';
                },
                columnsClasses: {
                    no: 'checkbox_parent'
                }
            },
        }
    },
    methods: {
        isComplete(task) {
            if (task.is_complete) {
                return { 'has-background-warning': true };
            }
        },
        toggleComplete(e) {
            if (this.isLoading) {
                return;
            }
            this.isLoading = true;
            let found = this.tasks.find(task => task.id == e.row.id);
            if (!found) {
                return;
            }
            found.is_complete = !found.is_complete;
            this.patch('/api/tasks', {
                words: [e.row.id],
                isComplete: e.row.is_complete
            });
        },
        toggleCheckAll() {
            this.checkAll = !this.checkAll;
            if (this.checkAll) {
                this.checkedWords = this.tasks.map(task => {
                    return task.id;
                });
                return;
            }
            this.checkedWords = [];
        },
        toggleCheck(id) {
            let index = this.checkedWords.findIndex(el => el == id);
            if (index != -1) {
                this.checkedWords.splice(index, 1);
                return;
            }
            this.checkedWords.push(id);
        },
        async onDelete() {
            let value = await swal("選択した" + this.checkedWords.length + "個の単語を削除しますか？", {
                buttons: {
                    ondelete: {
                        text: "削除する",
                        value: "ondelete"
                    },
                    not: "削除しない",
                }
            });

            if (value == 'ondelete') {
                let response = await axios.delete('/api/tasks', {
                    data: {
                        tasks: this.checkedWords
                    }
                });
                swal("削除しました。");
                this.get('/api/words/edit/' + this.id);
                this.refreshProjects();
                this.checkedWords = [];
            }
        },
        async onComplete() {
            let value = await swal("選択した" + this.checkedWords.length + "個の単語の学習状況を変更しますか？", {
                buttons: {
                    oncomplete: {
                        text: "学習済みにする",
                        value: "oncomplete"
                    },
                    ongoing: {
                        text: "学習中にする",
                        value: "ongoing"
                    },
                    not: "変更しない",
                }
            });

            if (value == 'oncomplete') {
                let response = await axios.patch('/api/tasks/', {
                    words: this.checkedWords,
                    isComplete: true
                });
                swal("学習済みにしました。");
            }
            if (value == 'ongoing') {
                let respones = await axios.patch('/api/tasks/', {
                    words: this.checkedWords,
                    isComplete: false
                });
                swal("学習中にしました。");
            }
            this.get('/api/words/edit/' + this.id);
            this.checkAll = false;
            this.checkedWords = [];
        },
        onExport() {
            let text = '';
            let tasks = this.tasks;
            if (this.exportOption == 'ongoing') {
                tasks = this.tasks.filter(el =>
                    el.is_complete == false
                );
            }
            if (this.exportOption == 'complete') {
                tasks = this.tasks.filter(el =>
                    el.is_complete == true
                );
            }

            tasks.forEach(el => {
                text += el.lemma + '\n';
            })
            let blob = new Blob([text], { type: 'text/csv' });
            let link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = this.book + '.txt';
            link.click();
        }
    },
    filters: {
        isComplete(value) {
            if (value) {
                return '済';
            }
            return '未';
        }
    },
    mounted() {
        this.get('/api/words/edit/' + this.id);
        this.get('/api/projects/name/' + this.id);
    }
}

</script>
