<template>
    <div>
        <h1 class="title">単語帳</h1>
        <form @submit.prevent="create">
            <div class="field is-grouped is-grouped-multiline">
                <label class="label" for="name">単語帳の名前（任意）</label>
                <div class="control">
                    <input type="text" class="input" name="name" placeholder="単語帳の名前（任意）" v-model="name" maxlength="50">
                </div>
                <label class="label" for="size">単語帳のサイズ(50～500の間で)</label>
                <div class="control">
                    <input type="number" min="50" max="500" step="50" class="input" name="size" v-model="size" required>
                </div>
                <div class="control">
                    <button type="submit" class="button is-link">単語帳を作成</button>
                </div>
            </div>
        </form>
        <form v-if="projectLength" @submit.prevent="onDelete">
            <table class="table is-hoverable is-responsive">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>単語帳</th>
                        <th>登録数</th>
                        <th>進捗</th>
                        <th>更新日時</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr @click="swtichActive(project)" v-for="(project, index) in projects" :key="index" :class="isActive(project)">
                        <td @click.self.stop="toggleDelete(project.id)"><label class="checkbox" :for="project.id" @click.stop>
                                <input type="checkbox" :id="project.id" :value="project.id" v-model="deleteProjects">{{ index + 1 }}
                            </label></td>
                        <td>{{ project.name }}</td>
                        <td>{{ project.task_count }}/{{ project.size }}</td>
                        <td>{{ getProgress(project) }}</td>
                        <td>{{ project.created_at }}</td>
                        <td>
                            <router-link :to="getEditLink(project.id)" tag="button" class="button is-primary">編集</router-link>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="field">
                <div class="control">
                    <button type="submit" class="button is-danger" :disabled="!deleteProjects.length">チェックした単語帳を削除</button>
                </div>
            </div>
        </form>
        <h1 class="title">単語リストのインポート</h1>
        <p>改行区切りまたはカンマ区切りの単語リストを単語帳にインポートできます。</p>
        <p>オプション</p>
        <form>
            <div @click="toggleAllowDuplicate">
                <label class="checkbox" for="allowDuplicate">
                    <input type="checkbox" name="allowDuplicate" v-model="allowDuplicate">単語の重複登録を許可する
                </label>
            </div>
            <div @click="toggleAsComplete">
                <label class="checkbox" for="asComplete">
                    <input type="checkbox" name="asComplete" v-model="asComplete">学習済みの単語として追加する
                </label>
            </div>
            <file-pond name="file" ref="pond" :server="{
             url: '/api/tasks/create',
             process: {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + $apiToken
                },
                onload: response => getInsertCount(response)
            }
        }" allow-multiple="false" instantUpload="true" allowRevert="false" allowReplace="true" maxFileSize="1MB" accepted-file-types="text/plain, text/csv, text/html" label-idle="ファイルを選択、またはドロップ（サイズは最大1MBまで）" @addfile="setMetaData" @processfile="onAddFile" @error="onError" />
        </form>
    </div>
</template>
<script>
import vueFilePond from 'vue-filepond';
import 'filepond/dist/filepond.min.css';
import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginFileMetadata from 'filepond-plugin-file-metadata';

import swal from 'sweetalert';
import { mixin } from '../mixin';

const FilePond = vueFilePond(
    FilePondPluginFileValidateSize,
    FilePondPluginFileValidateType,
    FilePondPluginFileMetadata
);

export default {
    components: {
        FilePond
    },
    computed: {
        projectLength() {
            if (!this.projects) {
                return;
            }
            return this.projects.length;
        },
        activeProject() {
            let found = this.projects.find(el =>
                el.is_active == true);
            if (!found) {
                return null;
            }
            return found.id;
        }
    },
    data() {
        return {
            level: '',
            count: '',
            name: '',
            size: 200,
            projects: [],
            deleteProjects: [],
            allowDuplicate: false,
            asComplete: false
        }
    },
    mixins: [mixin],
    methods: {
        create() {
            console.log('creating a book...');
            this.post('/api/projects', {
                name: this.name,
                size: this.size,
            })
            swal("新しい単語帳を作成しました。");
        },
        async fetchProjects() {
            let response = await axios.get('/api/projects');
            this.record(response.data);
            this.$ls.set('projects', this.projects, lsExpiryTime);
            this.$ls.set('level', this.level, lsExpiryTime);
        },
        getCompleteRate(project) {
            if (!project.task_count) {
                return null;
            }
            return parseInt(100 * project.task_complete_count / project.task_count) + '%';
        },
        getProgress(project) {
            if (!project.task_count) {
                return;
            }
            return project.task_complete_count + '/' +
                project.task_count + '(' +
                this.getCompleteRate(project) + ')';
        },
        isActive(project) {
            if (project.is_active) {
                return { 'has-background-warning': true };
            }
        },
        swtichActive(project) {
            if (this.activeProject == project.id) {
                return;
            }
            let found = this.projects.find(el =>
                el.id == this.activeProject);
            if (found) {
                found.is_active = false;
            }
            axios.patch('/api/projects/', { active: project.id });
            found = this.projects.find(el =>
                el.id == project.id);
            found.is_active = true;
            this.$ls.set('projects', this.projects, lsExpiryTime);
        },
        toggleDelete(id) {
            let index = this.deleteProjects.findIndex(el => el == id);
            if (index != -1) {
                this.deleteProjects.splice(index, 1);
                return;
            }
            this.deleteProjects.push(id);
        },
        toggleAsComplete() {
            this.asComplete = !this.asComplete;
        },
        toggleAllowDuplicate() {
            this.allowDuplicate = !this.allowDuplicate;
        },
        onDelete() {
            console.log('deleting...')
            swal("一度削除した単語帳は元に戻せません。本当に削除しますか？", {
                    buttons: {
                        not: "削除しない",
                        ondelete: {
                            text: "削除する",
                            value: "ondelete"
                        }
                    }
                })
                .then(value => {
                    if (value == 'ondelete') {
                        this.delete('/api/projects', { projects: this.deleteProjects });
                        swal("削除しました。");
                        this.deleteProjects = [];
                    }
                });
        },
        setMetaData() {
            this.$refs.pond.getFile().setMetadata({
                asComplete: this.asComplete,
                allowDuplicate: this.allowDuplicate
            });
            console.log('data setted...');
        },
        getInsertCount(response) {
            let data = JSON.parse(response);
            this.count = data.count;
        },
        onAddFile() {
            swal(this.count + '個の単語を単語帳に追加しました。');
            this.fetchProjects();
        },
        onError() {
            swal('アップロード失敗！');
        },
        getEditLink(id) {
            return '/edit/' + id;
        },
    },
    mounted() {
        if (this.$ls.get('projects')) {
            this.projects = this.$ls.get('projects');
            this.level = this.$ls.get('level');
            return;
        }
        this.fetchProjects();
    }
}

</script>
