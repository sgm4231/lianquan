<template>
  <div>
    <h4>基础设置</h4>
    <el-form label-position="top" :inline="false">
      <el-form-item label="项目名称">
        <el-input type="text" placeholder="请输入项目名称" v-model="project.name" :disabled="submitting"></el-input>
      </el-form-item>
      <el-form-item label="项目描述">
        <el-input type="textarea" :rows="3" placeholder="请输入项目描述" v-model="project.desc" :disabled="submitting"></el-input>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" :loading="submitting" @click="handleSubmit">修改信息</el-button>
      </el-form-item>
    </el-form>

    <h4>移除项目</h4>
    <el-alert
      title="移除这个项目"
      description="移除这个项目，将会删除平台中所有的关联数据，并且再次添加项目的时候旧记录不复存在。"
      type="warning"
      :show-icon="true"
    >
    </el-alert>
    <el-button :loading="deleting" @click="handleDestory" type="danger" style="margin-top: 24px;">移除项目</el-button>
  </div>
</template>

<script>
import { update, destory } from '../../../api/projects';
import PlusMessageBundle from 'plus-message-bundle';
export default {
  name: 'module-project-settings',
  props: {
    project: { required: true, type: Object },
  },
  data: () => ({
    submitting: false,
    deleting: false,
  }),
  methods: {
    /**
     * Submit base project info change handle.
     *
     * @return {void}
     * @author Seven Du <shiweidu@outlook.com>
     */
    handleSubmit() {
      this.submitting = true
      update(this.project.id, this.project).then(() => {
        this.submitting = false;
        this.$notify({
          title: '成功',
          message: '修改基本信息成功',
          type: 'success',
        });
      }).catch(({ response: { data = {} } = {} }) => {
        this.submitting = false;
        const message = PlusMessageBundle(data, '修改失败');
        this.$notify({
          title: '错误',
          message: message.getMessage(),
          type: 'error',
        });
      });
    },

    /**
     * Destory a project handle.
     *
     * @return {void}
     * @author Seven Du <shiweidu@outlook.com>
     */
    handleDestory() {
      this.$confirm('确定要移除项目吗？', '提示', {
        type: 'warning',
        confirmButtonText: '移除项目',
        cancelButtonText: '取消',
        center: true,
      }).then(() => {
        this.deleting = true;
        destory(this.project.id).then(() => {
          this.$notify({
            title: '成功',
            message: '移除项目成功',
            type: 'success',
          });
          this.$router.replace('/projects');
        }).catch(({ response: { data = {} } = {} }) => {
          this.deleting = false;
          const message = PlusMessageBundle(data, '移除失败');
          this.$notify({
            title: '错误',
            message: message.getMessage(),
            type: 'error',
          });
        });
      }).catch(() => {
        this.deleting = false;
      });
    },
  },
};
</script>
