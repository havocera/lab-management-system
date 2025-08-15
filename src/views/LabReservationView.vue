<template>
  <div class="reservation-management">
    <!-- 页面头部 -->
    <div class="page-header">
      <div class="header-content">
        <div class="title-section">
          <div class="i-carbon-calendar text-3xl text-indigo-600" />
          <div>
            <h1 class="page-title">实验室预约</h1>
            <p class="page-subtitle">查看和管理实验室预约信息</p>
          </div>
        </div>
        <el-radio-group v-model="activeTab" size="large" class="tab-switch">
          <el-radio-button label="labs">
            <div class="i-carbon-chemistry mr-1" />
            实验室列表
          </el-radio-button>
          <el-radio-button label="my">
            <div class="i-carbon-user mr-1" />
            我的预约
          </el-radio-button>
          <el-radio-button v-if="isTeacher || hasManagerLabs" label="all">
            <div class="i-carbon-settings mr-1" />
            预约管理
          </el-radio-button>
        </el-radio-group>
      </div>
    </div>

    <!-- 实验室列表 -->
    <div v-if="activeTab === 'labs'">
      <!-- 搜索区域 -->
      <el-card class="search-card">
        <template #header>
          <div class="card-header">
            <div class="i-carbon-search mr-2" />
            <span>搜索实验室</span>
          </div>
        </template>
        <el-form :model="searchForm" inline class="search-form">
          <el-form-item>
            <el-input
              v-model="searchForm.name"
              placeholder="请输入实验室名称"
              clearable
              class="search-input"
            >
              <template #prefix>
                <div class="i-carbon-chemistry text-gray-400" />
              </template>
            </el-input>
          </el-form-item>
          <el-form-item>
            <el-input
              v-model="searchForm.location"
              placeholder="请输入位置"
              clearable
              class="search-input"
            >
              <template #prefix>
                <div class="i-carbon-location text-gray-400" />
              </template>
            </el-input>
          </el-form-item>
          <el-form-item>
            <el-select 
              v-model="searchForm.status" 
              placeholder="请选择状态" 
              clearable
              class="search-select"
            >
              <template #prefix>
                <div class="i-carbon-status text-gray-400" />
              </template>
              <el-option label="可用" value="0" />
              <el-option label="不可用" value="1" />
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="handleSearch" class="search-btn">
              <div class="i-carbon-search mr-1" />
              搜索
            </el-button>
            <el-button @click="handleReset" class="reset-btn">
              <div class="i-carbon-reset mr-1" />
              重置
            </el-button>
          </el-form-item>
        </el-form>
      </el-card>

      <!-- 实验室卡片列表 -->
      <div class="labs-grid">
        <!-- 空状态提示 -->
        <div v-if="!loading && filteredLabList.length === 0" class="empty-state">
          <div class="empty-icon">
            <div class="i-carbon-chemistry text-6xl text-gray-300" />
          </div>
          <div class="empty-text">
            <h3>暂无实验室数据</h3>
            <p v-if="searchForm.name || searchForm.location || searchForm.status">
              尝试调整搜索条件或
              <el-button type="primary" link @click="handleReset">清空筛选</el-button>
            </p>
            <p v-else>系统中还没有实验室信息</p>
          </div>
        </div>
        
        <div v-for="lab in filteredLabList" :key="lab.id" class="lab-card">
          <div class="lab-card-header">
            <div class="lab-status">
              <el-tag 
                :type="lab.status === '0' ? 'success' : 'danger'"
                :class="`status-${lab.status}`"
                size="small"
              >
                {{ lab.status === '0' ? '可用' : '不可用' }}
              </el-tag>
            </div>
            <div class="lab-capacity">
              <div class="i-carbon-user-multiple mr-1" />
              {{ lab.capacity }}人
            </div>
          </div>
          
          <div class="lab-info">
            <h3 class="lab-name">{{ lab.name }}</h3>
            <div class="lab-location">
              <div class="i-carbon-location mr-1" />
              {{ lab.location }}
            </div>
          </div>

          <div class="lab-manager" v-if="getLabManagerByLab(lab)">
            <h4>负责人信息</h4>
            <div class="manager-info">
              <div class="manager-name">
                <div class="i-carbon-user mr-1" />
                {{ getLabManagerByLab(lab).name }}
              </div>
              <div class="manager-contacts">
                <div 
                  v-if="getLabManagerByLab(lab).phone" 
                  class="contact-item" 
                  @click="callPhone(getLabManagerByLab(lab).phone)"
                >
                  <div class="i-carbon-phone mr-1" />
                  <span>{{ formatPhone(getLabManagerByLab(lab).phone) }}</span>
                </div>
                <div 
                  v-if="getLabManagerByLab(lab).email" 
                  class="contact-item" 
                  @click="sendEmail(getLabManagerByLab(lab).email)"
                >
                  <div class="i-carbon-email mr-1" />
                  <span>{{ getLabManagerByLab(lab).email }}</span>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="lab-manager">
            <span class="no-manager">未设置负责人</span>
          </div>

          <div class="lab-actions">
            <div class="action-buttons">
              <el-button 
                type="primary" 
                :disabled="lab.status !== '0'"
                @click="handleReserve(lab)"
                class="reserve-btn"
              >
                <div class="i-carbon-calendar-add mr-1" />
                {{ lab.status === '0' ? '预约使用' : '暂不可用' }}
              </el-button>
              
              <div class="view-buttons">
                <el-button 
                  type="info" 
                  plain
                  size="small"
                  @click="handleViewEquipment(lab)"
                  class="view-btn"
                >
                  <div class="i-carbon-machine-learning mr-1" />
                  查看设备
                </el-button>
                <el-button 
                  type="success" 
                  plain
                  size="small"
                  @click="handleViewReagent(lab)"
                  class="view-btn"
                >
                  <div class="i-carbon-chemistry mr-1" />
                  查看试剂
                </el-button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 我的预约 -->
    <div v-if="activeTab === 'my'">
      <!-- 搜索区域 -->
      <el-card class="search-card">
        <template #header>
          <div class="card-header">
            <div class="i-carbon-search mr-2" />
            <span>筛选我的预约</span>
          </div>
        </template>
        <el-form :model="searchForm" inline class="search-form">
          <el-form-item>
            <el-select 
              v-model="searchForm.status" 
              placeholder="请选择状态" 
              clearable
              class="search-select"
            >
              <template #prefix>
                <div class="i-carbon-status text-gray-400" />
              </template>
              <el-option label="待审核" value="pending" />
              <el-option label="已批准" value="approved" />
              <el-option label="已拒绝" value="rejected" />
              <el-option label="已完成" value="completed" />
              <el-option label="已取消" value="cancelled" />
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-date-picker
              v-model="searchForm.timeRange"
              type="datetimerange"
              range-separator="至"
              start-placeholder="开始时间"
              end-placeholder="结束时间"
              value-format="YYYY-MM-DD HH:mm:ss"
              class="search-date"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="handleSearch" class="search-btn">
              <div class="i-carbon-search mr-1" />
              搜索
            </el-button>
            <el-button @click="handleReset" class="reset-btn">
              <div class="i-carbon-reset mr-1" />
              重置
            </el-button>
          </el-form-item>
        </el-form>
      </el-card>

      <!-- 预约卡片列表 -->
      <div class="reservations-grid">
        <!-- 空状态提示 -->
        <div v-if="!loading && myReservations.length === 0" class="empty-state">
          <div class="empty-icon">
            <div class="i-carbon-calendar text-6xl text-gray-300" />
          </div>
          <div class="empty-text">
            <h3>暂无预约记录</h3>
            <p>您还没有任何实验室预约记录</p>
          </div>
        </div>
        
        <div v-for="reservation in myReservations" :key="reservation.id" class="reservation-card">
          <div class="reservation-header">
            <div class="reservation-status">
              <el-tag 
                :type="getStatusType(reservation.status)"
                :class="`status-${reservation.status}`"
                size="large"
              >
                {{ getStatusText(reservation.status) }}
              </el-tag>
            </div>
            <div class="reservation-time">
              <div class="i-carbon-time mr-1" />
              {{ formatDateTime(reservation.start_time) }}
            </div>
          </div>
          
          <div class="reservation-content">
            <h3 class="lab-name">{{ reservation.lab_name }}</h3>
            
            <div class="time-info">
              <div class="time-item">
                <div class="i-carbon-calendar mr-1" />
                <span>{{ formatDateTime(reservation.start_time) }}</span>
              </div>
              <div class="time-separator">至</div>
              <div class="time-item">
                <div class="i-carbon-calendar mr-1" />
                <span>{{ formatDateTime(reservation.end_time) }}</span>
              </div>
            </div>

            <div class="purpose-info">
              <div class="purpose-label">
                <div class="i-carbon-document mr-1" />
                用途说明
              </div>
              <div class="purpose-text">{{ reservation.purpose || '暂无说明' }}</div>
            </div>

            <div class="manager-section" v-if="getLabManager(reservation.lab_id)">
              <h4>负责人信息</h4>
              <div class="manager-info">
                <div class="manager-name">
                  <div class="i-carbon-user mr-1" />
                  {{ getLabManager(reservation.lab_id).name }}
                </div>
                <div class="manager-contacts">
                  <div 
                    v-if="getLabManager(reservation.lab_id).phone" 
                    class="contact-item" 
                    @click="callPhone(getLabManager(reservation.lab_id).phone)"
                  >
                    <div class="i-carbon-phone mr-1" />
                    <span>{{ formatPhone(getLabManager(reservation.lab_id).phone) }}</span>
                  </div>
                  <div 
                    v-if="getLabManager(reservation.lab_id).email" 
                    class="contact-item" 
                    @click="sendEmail(getLabManager(reservation.lab_id).email)"
                  >
                    <div class="i-carbon-email mr-1" />
                    <span>{{ getLabManager(reservation.lab_id).email }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="no-manager">
              <span class="text-gray-400">未设置负责人</span>
            </div>
          </div>

          <div class="reservation-actions">
            <el-button 
              v-if="reservation.status === 'approved'"
              type="success"
              @click="handleFillUsageRecord(reservation)"
              class="usage-btn"
            >
              <div class="i-carbon-document-add mr-1" />
              填写使用记录
            </el-button>
            <el-button 
              v-if="reservation.status === 'pending' || reservation.status === 'approved'"
              type="danger" 
              @click="handleCancel(reservation)"
              class="cancel-btn"
            >
              <div class="i-carbon-close mr-1" />
              取消预约
            </el-button>
            <el-button 
              v-if="reservation.status === 'completed'"
              type="info" 
              @click="handleViewUsageRecord(reservation)"
              class="view-btn"
            >
              <div class="i-carbon-view mr-1" />
              查看使用记录
            </el-button>
            <el-button 
              type="info" 
              plain
              size="small"
              @click="handleViewReservationDetail(reservation)"
              class="detail-btn"
            >
              <div class="i-carbon-view mr-1" />
              查看详情
            </el-button>
          </div>
        </div>
      </div>

      <!-- 分页 -->
      <div class="pagination-wrapper">
        <el-pagination
          v-model:current-page="myPage"
          v-model:page-size="myLimit"
          :total="myTotal"
          :page-sizes="[10, 20, 50, 100]"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="getMyReservationsList"
          @current-change="getMyReservationsList"
        />
      </div>
    </div>

    <!-- 预约管理（管理员） -->
    <div v-if="activeTab === 'all'">
      <!-- 搜索区域 -->
      <el-card class="search-card">
        <template #header>
          <div class="card-header">
            <div class="i-carbon-search mr-2" />
            <span>筛选预约记录</span>
          </div>
        </template>
        <el-form :model="searchForm" inline class="search-form">
          <el-form-item>
            <el-select 
              v-model="searchForm.lab_id" 
              placeholder="请选择实验室" 
              clearable
              class="search-select"
            >
              <template #prefix>
                <div class="i-carbon-chemistry text-gray-400" />
              </template>
              <el-option
                v-for="lab in availableLabsForSearch"
                :key="lab.id"
                :label="lab.name"
                :value="lab.id"
              />
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-select 
              v-model="searchForm.status" 
              placeholder="请选择状态" 
              clearable
              class="search-select"
            >
              <template #prefix>
                <div class="i-carbon-status text-gray-400" />
              </template>
              <el-option label="待审核" value="pending" />
              <el-option label="已批准" value="approved" />
              <el-option label="已拒绝" value="rejected" />
              <el-option label="已完成" value="completed" />
              <el-option label="已取消" value="cancelled" />
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-date-picker
              v-model="searchForm.timeRange"
              type="datetimerange"
              range-separator="至"
              start-placeholder="开始时间"
              end-placeholder="结束时间"
              value-format="YYYY-MM-DD HH:mm:ss"
              class="search-date"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="handleSearch" class="search-btn">
              <div class="i-carbon-search mr-1" />
              搜索
            </el-button>
            <el-button @click="resetSearch" class="reset-btn">
              <div class="i-carbon-reset mr-1" />
              重置
            </el-button>
          </el-form-item>
        </el-form>
      </el-card>

      <!-- 预约管理卡片列表 -->
      <div class="reservations-grid">
        <!-- 空状态提示 -->
        <div v-if="!loading && allReservations.length === 0" class="empty-state">
          <div class="empty-icon">
            <div class="i-carbon-calendar-tools text-6xl text-gray-300" />
          </div>
          <div class="empty-text">
            <h3>暂无预约记录</h3>
            <p v-if="searchForm.lab_id || searchForm.status || searchForm.timeRange?.length">
              没有符合条件的预约记录，尝试
              <el-button type="primary" link @click="resetSearch">清空筛选</el-button>
            </p>
            <p v-else>暂时没有需要管理的预约记录</p>
          </div>
        </div>
        
        <div v-for="reservation in allReservations" :key="reservation.id" class="reservation-card admin">
          <div class="reservation-header">
            <div class="reservation-status">
              <el-tag 
                :type="getStatusType(reservation.status)"
                :class="`status-${reservation.status}`"
                size="large"
              >
                {{ getStatusText(reservation.status) }}
              </el-tag>
            </div>
            <div class="reservation-priority" v-if="reservation.status === 'pending'">
              <el-tag type="warning" size="small">
                <div class="i-carbon-warning mr-1" />
                待审核
              </el-tag>
            </div>
          </div>
          
          <div class="reservation-content">
            <div class="lab-and-user">
              <h3 class="lab-name">{{ reservation.lab_name }}</h3>
              <div class="user-info">
                <div class="i-carbon-user mr-1" />
                <span>预约人：{{ reservation.user_name }}</span>
              </div>
            </div>
            
            <div class="time-info">
              <div class="time-item">
                <div class="i-carbon-calendar mr-1" />
                <span>{{ formatDateTime(reservation.start_time) }}</span>
              </div>
              <div class="time-separator">至</div>
              <div class="time-item">
                <div class="i-carbon-calendar mr-1" />
                <span>{{ formatDateTime(reservation.end_time) }}</span>
              </div>
            </div>

            <div class="purpose-info">
              <div class="purpose-label">
                <div class="i-carbon-document mr-1" />
                用途说明
              </div>
              <div class="purpose-text">{{ reservation.purpose || '暂无说明' }}</div>
            </div>

            <div class="manager-section" v-if="getLabManager(reservation.lab_id)">
              <h4>负责人信息</h4>
              <div class="manager-info">
                <div class="manager-name">
                  <div class="i-carbon-user mr-1" />
                  {{ getLabManager(reservation.lab_id).name }}
                </div>
                <div class="manager-contacts">
                  <div 
                    v-if="getLabManager(reservation.lab_id).phone" 
                    class="contact-item" 
                    @click="callPhone(getLabManager(reservation.lab_id).phone)"
                  >
                    <div class="i-carbon-phone mr-1" />
                    <span>{{ formatPhone(getLabManager(reservation.lab_id).phone) }}</span>
                  </div>
                  <div 
                    v-if="getLabManager(reservation.lab_id).email" 
                    class="contact-item" 
                    @click="sendEmail(getLabManager(reservation.lab_id).email)"
                  >
                    <div class="i-carbon-email mr-1" />
                    <span>{{ getLabManager(reservation.lab_id).email }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="no-manager">
              <span class="text-gray-400">未设置负责人</span>
            </div>
          </div>

          <div class="reservation-actions">
            <div v-if="reservation.status === 'pending' && canReview(reservation)" class="review-actions">
              <el-button 
                type="success" 
                @click="handleReview(reservation, 'approved')"
                class="approve-btn"
              >
                <div class="i-carbon-checkmark mr-1" />
                批准
              </el-button>
              <el-button 
                type="danger" 
                @click="handleReview(reservation, 'rejected')"
                class="reject-btn"
              >
                <div class="i-carbon-close mr-1" />
                拒绝
              </el-button>
            </div>
            <div v-else-if="reservation.status === 'pending' && !canReview(reservation)" class="no-permission">
              <el-tag type="info" size="small">
                <div class="i-carbon-locked mr-1" />
                仅负责人可审核
              </el-tag>
            </div>
            <el-button 
              type="info" 
              plain
              size="small"
              @click="handleViewReservationDetail(reservation)"
              class="detail-btn"
            >
              <div class="i-carbon-view mr-1" />
              查看详情
            </el-button>
            <el-button 
              v-if="reservation.status === 'completed'"
              type="primary" 
              plain
              size="small"
              @click="handleViewUsageRecord(reservation)"
              class="view-usage-btn"
            >
              <div class="i-carbon-document mr-1" />
              查看使用记录
            </el-button>
          </div>
        </div>
      </div>

      <!-- 分页 -->
      <div class="pagination-wrapper">
        <el-pagination
          v-model:current-page="allPage"
          v-model:page-size="allLimit"
          :total="allTotal"
          :page-sizes="[10, 20, 50, 100]"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="getAllReservations"
          @current-change="getAllReservations"
        />
      </div>
    </div>

    <!-- 使用记录填写对话框 -->
    <el-dialog
      v-model="usageRecordDialogVisible"
      title="填写使用记录"
      width="600px"
    >
      <div class="usage-record-form">
        <div class="reservation-info">
          <h4>预约信息</h4>
          <div class="info-item">
            <span class="label">实验室：</span>
            <span>{{ currentReservation?.lab_name }}</span>
          </div>
          <div class="info-item">
            <span class="label">预约时间：</span>
            <span>{{ formatDateTime(currentReservation?.start_time) }} 至 {{ formatDateTime(currentReservation?.end_time) }}</span>
          </div>
        </div>

        <el-form :model="usageRecordForm" label-width="120px">
          <div class="form-section">
            <h4>使用情况</h4>
            <el-form-item label="实际开始时间">
              <el-date-picker
                v-model="usageRecordForm.actual_start_time"
                type="datetime"
                placeholder="选择实际开始时间"
                style="width: 100%"
                value-format="YYYY-MM-DD HH:mm:ss"
              />
            </el-form-item>
            <el-form-item label="实际结束时间">
              <el-date-picker
                v-model="usageRecordForm.actual_end_time"
                type="datetime"
                placeholder="选择实际结束时间"
                style="width: 100%"
                value-format="YYYY-MM-DD HH:mm:ss"
              />
            </el-form-item>
          </div>

          <div class="form-section">
            <h4>检查项目</h4>
            <el-form-item label="是否断电">
              <el-radio-group v-model="usageRecordForm.power_off">
                <el-radio :label="1">是</el-radio>
                <el-radio :label="0">否</el-radio>
              </el-radio-group>
            </el-form-item>
            <el-form-item label="空调是否关闭">
              <el-radio-group v-model="usageRecordForm.air_conditioning_off">
                <el-radio :label="1">是</el-radio>
                <el-radio :label="0">否</el-radio>
              </el-radio-group>
            </el-form-item>
            <el-form-item label="卫生是否完成">
              <el-radio-group v-model="usageRecordForm.hygiene_completed">
                <el-radio :label="1">是</el-radio>
                <el-radio :label="0">否</el-radio>
              </el-radio-group>
            </el-form-item>
            <el-form-item label="设备是否正常">
              <el-radio-group v-model="usageRecordForm.equipment_normal">
                <el-radio :label="1">是</el-radio>
                <el-radio :label="0">否</el-radio>
              </el-radio-group>
            </el-form-item>
          </div>

          <div class="form-section">
            <h4>备注信息</h4>
            <el-form-item label="设备问题描述" v-if="usageRecordForm.equipment_normal === 0">
              <el-input
                v-model="usageRecordForm.equipment_issues"
                type="textarea"
                :rows="3"
                placeholder="请描述设备存在的问题"
              />
            </el-form-item>
            <el-form-item label="其他备注">
              <el-input
                v-model="usageRecordForm.other_notes"
                type="textarea"
                :rows="3"
                placeholder="其他需要说明的情况"
              />
            </el-form-item>
          </div>
        </el-form>
      </div>
      <template #footer>
        <el-button @click="usageRecordDialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="usageRecordSubmitLoading" @click="handleSubmitUsageRecord">
          提交记录
        </el-button>
      </template>
    </el-dialog>

    <!-- 使用记录查看对话框 -->
    <el-dialog
      v-model="usageRecordViewDialogVisible"
      title="使用记录详情"
      width="600px"
    >
      <div class="usage-record-detail" v-if="usageRecordDetail">
        <div class="detail-section">
          <h4>预约信息</h4>
          <el-descriptions :column="2" border>
            <el-descriptions-item label="实验室">{{ usageRecordDetail.lab_name }}</el-descriptions-item>
            <el-descriptions-item label="房间号">{{ usageRecordDetail.room_no }}</el-descriptions-item>
            <el-descriptions-item label="使用人">{{ usageRecordDetail.user_name }}</el-descriptions-item>
            <el-descriptions-item label="用途">{{ usageRecordDetail.purpose }}</el-descriptions-item>
          </el-descriptions>
        </div>

        <div class="detail-section">
          <h4>时间信息</h4>
          <el-descriptions :column="2" border>
            <el-descriptions-item label="计划开始时间">{{ formatDateTime(usageRecordDetail.scheduled_start_time) }}</el-descriptions-item>
            <el-descriptions-item label="计划结束时间">{{ formatDateTime(usageRecordDetail.scheduled_end_time) }}</el-descriptions-item>
            <el-descriptions-item label="实际开始时间">{{ formatDateTime(usageRecordDetail.actual_start_time) }}</el-descriptions-item>
            <el-descriptions-item label="实际结束时间">{{ formatDateTime(usageRecordDetail.actual_end_time) }}</el-descriptions-item>
          </el-descriptions>
        </div>

        <div class="detail-section">
          <h4>使用情况</h4>
          <el-descriptions :column="2" border>
            <el-descriptions-item label="是否断电">
              <el-tag :type="usageRecordDetail.power_off ? 'success' : 'warning'">
                {{ usageRecordDetail.power_off ? '是' : '否' }}
              </el-tag>
            </el-descriptions-item>
            <el-descriptions-item label="空调是否关闭">
              <el-tag :type="usageRecordDetail.air_conditioning_off ? 'success' : 'warning'">
                {{ usageRecordDetail.air_conditioning_off ? '是' : '否' }}
              </el-tag>
            </el-descriptions-item>
            <el-descriptions-item label="卫生是否完成">
              <el-tag :type="usageRecordDetail.hygiene_completed ? 'success' : 'warning'">
                {{ usageRecordDetail.hygiene_completed ? '是' : '否' }}
              </el-tag>
            </el-descriptions-item>
            <el-descriptions-item label="设备是否正常">
              <el-tag :type="usageRecordDetail.equipment_normal ? 'success' : 'danger'">
                {{ usageRecordDetail.equipment_normal ? '是' : '否' }}
              </el-tag>
            </el-descriptions-item>
          </el-descriptions>
        </div>

        <div class="detail-section" v-if="usageRecordDetail.equipment_issues">
          <h4>设备问题描述</h4>
          <div class="content-text">{{ usageRecordDetail.equipment_issues }}</div>
        </div>

        <div class="detail-section" v-if="usageRecordDetail.other_notes">
          <h4>其他备注</h4>
          <div class="content-text">{{ usageRecordDetail.other_notes }}</div>
        </div>
      </div>
    </el-dialog>

    <!-- 预约详情对话框 -->
    <el-dialog
      v-model="reservationDetailDialogVisible"
      title="预约详情"
      width="700px"
    >
      <div class="reservation-detail" v-if="reservationDetail">
        <div class="detail-section">
          <h4>基本信息</h4>
          <el-descriptions :column="2" border>
            <el-descriptions-item label="预约编号">{{ reservationDetail.id }}</el-descriptions-item>
            <el-descriptions-item label="实验室">{{ reservationDetail.lab_name }}</el-descriptions-item>
            <el-descriptions-item label="预约人">{{ reservationDetail.user_name || '当前用户' }}</el-descriptions-item>
            <el-descriptions-item label="预约状态">
              <el-tag :type="getStatusType(reservationDetail.status)">
                {{ getStatusText(reservationDetail.status) }}
              </el-tag>
            </el-descriptions-item>
          </el-descriptions>
        </div>

        <div class="detail-section">
          <h4>时间安排</h4>
          <el-descriptions :column="2" border>
            <el-descriptions-item label="开始时间">{{ formatDateTime(reservationDetail.start_time) }}</el-descriptions-item>
            <el-descriptions-item label="结束时间">{{ formatDateTime(reservationDetail.end_time) }}</el-descriptions-item>
            <el-descriptions-item label="预约时间">{{ formatDateTime(reservationDetail.create_time) }}</el-descriptions-item>
            <el-descriptions-item label="更新时间" v-if="reservationDetail.update_time">{{ formatDateTime(reservationDetail.update_time) }}</el-descriptions-item>
          </el-descriptions>
        </div>

        <div class="detail-section" v-if="reservationDetail.purpose">
          <h4>用途说明</h4>
          <div class="content-text">{{ reservationDetail.purpose }}</div>
        </div>

        <div class="detail-section" v-if="reservationDetail.manager_info">
          <h4>实验室负责人</h4>
          <el-descriptions :column="2" border>
            <el-descriptions-item label="负责人">{{ reservationDetail.manager_info.name }}</el-descriptions-item>
            <el-descriptions-item label="联系电话" v-if="reservationDetail.manager_info.phone">
              <div class="contact-item" @click="callPhone(reservationDetail.manager_info.phone)">
                <div class="i-carbon-phone mr-1" />
                <span>{{ formatPhone(reservationDetail.manager_info.phone) }}</span>
              </div>
            </el-descriptions-item>
            <el-descriptions-item label="邮箱" v-if="reservationDetail.manager_info.email" span="2">
              <div class="contact-item" @click="sendEmail(reservationDetail.manager_info.email)">
                <div class="i-carbon-email mr-1" />
                <span>{{ reservationDetail.manager_info.email }}</span>
              </div>
            </el-descriptions-item>
          </el-descriptions>
        </div>

        <div class="detail-section" v-if="reservationDetail.status === 'completed'">
          <h4>操作按钮</h4>
          <div class="action-section">
            <el-button type="primary" @click="handleViewUsageRecord(reservationDetail)">
              <div class="i-carbon-document mr-1" />
              查看使用记录
            </el-button>
          </div>
        </div>
      </div>
    </el-dialog>

    <!-- 设备查看对话框 -->
    <el-dialog
      v-model="equipmentDialogVisible"
      :title="`${currentLab?.name} - 设备列表`"
      width="900px"
      destroy-on-close
    >
      <div class="equipment-list">
        <el-table 
          :data="equipmentList" 
          v-loading="equipmentLoading"
          stripe
          max-height="400"
        >
          <el-table-column prop="name" label="设备名称" min-width="150" />
          <el-table-column prop="model" label="型号" width="120" />
          <el-table-column prop="manufacturer" label="制造商" width="120" />
          <el-table-column label="状态" width="100" align="center">
            <template #default="scope">
              <el-tag :type="scope.row.status === 'normal' ? 'success' : scope.row.status === 'maintenance' ? 'warning' : 'danger'">
                {{ getEquipmentStatusText(scope.row.status) }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="location" label="位置" width="120" />
          <el-table-column prop="purchase_date" label="采购日期" width="120" />
          <el-table-column prop="description" label="描述" min-width="150" show-overflow-tooltip />
        </el-table>
        
        <div v-if="!equipmentLoading && equipmentList.length === 0" class="empty-state">
          <div class="empty-icon">
            <div class="i-carbon-machine-learning text-4xl text-gray-300" />
          </div>
          <div class="empty-text">
            <p>该实验室暂无设备信息</p>
          </div>
        </div>
      </div>
    </el-dialog>

    <!-- 试剂查看对话框 -->
    <el-dialog
      v-model="reagentDialogVisible"
      :title="`${currentLab?.name} - 试剂列表`"
      width="900px"
      destroy-on-close
    >
      <div class="reagent-list">
        <el-table 
          :data="reagentList" 
          v-loading="reagentLoading"
          stripe
          max-height="400"
        >
          <el-table-column prop="name" label="试剂名称" min-width="150" />
          <el-table-column prop="code" label="编号" width="120" />
          <el-table-column label="危险等级" width="100" align="center">
            <template #default="scope">
              <el-tag :type="getDangerLevelType(scope.row.danger_level)" size="small">
                {{ getDangerLevelText(scope.row.danger_level) }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column label="库存" width="120" align="center">
            <template #default="scope">
              <span :class="getStockClass(scope.row.stock, scope.row.min_stock)">
                {{ scope.row.stock }} {{ scope.row.unit }}
              </span>
            </template>
          </el-table-column>
          <el-table-column prop="expiry_date" label="有效期" width="120">
            <template #default="scope">
              <span v-if="scope.row.expiry_date" :class="getExpiryClass(scope.row.expiry_date)">
                {{ scope.row.expiry_date }}
              </span>
              <span v-else class="text-gray-400">无</span>
            </template>
          </el-table-column>
          <el-table-column prop="keeper" label="保管人" width="100" />
          <el-table-column prop="location" label="存放位置" width="120" />
          <el-table-column prop="safety_info" label="安全说明" min-width="150" show-overflow-tooltip />
        </el-table>
        
        <div v-if="!reagentLoading && reagentList.length === 0" class="empty-state">
          <div class="empty-icon">
            <div class="i-carbon-chemistry text-4xl text-gray-300" />
          </div>
          <div class="empty-text">
            <p>该实验室暂无试剂信息</p>
          </div>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Phone, Message } from '@element-plus/icons-vue'
import { getLabList } from '@/api/lab'
import { getReservationList, getMyReservations, cancelReservation, reviewReservation } from '@/api/reservation'
import { createUsageRecord, getUsageRecord } from '@/api/usageRecord'
import { getEquipmentList } from '@/api/equipment'
import { getReagentList } from '@/api/reagent'
import { useUserStore } from '@/stores/user'

const router = useRouter()
const userStore = useUserStore()
const isAdmin = computed(() => userStore.roles.includes('admin'))
const isTeacher = computed(() => userStore.roles.includes('teacher'))

// 检查当前用户是否为指定实验室的负责人
const isLabManager = (labId) => {
  if (!userStore.userInfo || !labList.value) return false
  const lab = labList.value.find(l => l.id === labId)
  return lab && lab.manager_id === userStore.userInfo.id
}

// 检查用户是否有审核权限（管理员或对应实验室负责人）
const canReview = (reservation) => {
  return isAdmin.value || isLabManager(reservation.lab_id)
}

// 检查当前用户是否为任意实验室的负责人
const hasManagerLabs = computed(() => {
  if (!userStore.userInfo || !labList.value) return false
  return labList.value.some(lab => lab.manager_id === userStore.userInfo.id)
})

// 搜索时可选择的实验室列表（管理员看到所有，负责人只看到自己负责的）
const availableLabsForSearch = computed(() => {
  if (isAdmin.value) {
    return labList.value
  }
  if (!userStore.userInfo || !labList.value) return []
  return labList.value.filter(lab => lab.manager_id === userStore.userInfo.id)
})

// 过滤后的实验室列表（用于显示）
const filteredLabList = computed(() => {
  if (!labList.value) return []
  
  return labList.value.filter(lab => {
    // 名称过滤
    if (searchForm.name && !lab.name.toLowerCase().includes(searchForm.name.toLowerCase())) {
      return false
    }
    
    // 位置过滤
    if (searchForm.location && !lab.location?.toLowerCase().includes(searchForm.location.toLowerCase())) {
      return false
    }
    
    // 状态过滤
    if (searchForm.status && lab.status !== searchForm.status) {
      return false
    }
    
    return true
  })
})

// 获取实验室负责人信息（通过lab_id）
const getLabManager = (labId) => {
  if (!labList.value || !labId) return null
  
  const lab = labList.value.find(l => l.id === labId)
  if (!lab) return null
  
  // 如果没有负责人ID，返回null
  if (!lab.manager_id) return null
  
  // 返回负责人信息，支持多种可能的字段名称
  return {
    name: lab.manager_name || lab.managerName || '负责人',
    phone: lab.manager_phone || lab.managerPhone || lab.phone || '',
    email: lab.manager_email || lab.managerEmail || lab.email || ''
  }
}

// 获取实验室负责人信息（直接通过实验室对象）
const getLabManagerByLab = (lab) => {
  if (!lab) return null
  
  // 如果没有负责人ID，返回null
  if (!lab.manager_id) return null
  
  // 返回负责人信息，支持多种可能的字段名称
  return {
    name: lab.manager_name || lab.managerName || '负责人',
    phone: lab.manager_phone || lab.managerPhone || lab.phone || '',
    email: lab.manager_email || lab.managerEmail || lab.email || ''
  }
}

// 格式化电话号码显示
const formatPhone = (phone) => {
  if (!phone) return ''
  // 简单的电话号码格式化，如 13812345678 -> 138****5678
  if (phone.length === 11) {
    return phone.substr(0, 3) + '****' + phone.substr(7)
  }
  return phone
}

// 拨打电话
const callPhone = (phone) => {
  if (phone) {
    window.location.href = `tel:${phone}`
  }
}

// 发送邮件
const sendEmail = (email) => {
  if (email) {
    window.location.href = `mailto:${email}`
  }
}

const loading = ref(false)
const labList = ref([])
const activeTab = ref('labs')

// 我的预约
const myReservations = ref([])
const myPage = ref(1)
const myLimit = ref(10)
const myTotal = ref(0)

// 所有预约（管理员）
const allReservations = ref([])
const allPage = ref(1)
const allLimit = ref(10)
const allTotal = ref(0)
const searchForm = reactive({
  name: '',
  location: '',
  status: '',
  lab_id: '',
  timeRange: []
})

// 使用记录相关数据
const usageRecordDialogVisible = ref(false)
const usageRecordViewDialogVisible = ref(false)
const usageRecordForm = reactive({
  reservation_id: '',
  power_off: 1,
  air_conditioning_off: 1,
  hygiene_completed: 1,
  equipment_normal: 1,
  equipment_issues: '',
  other_notes: '',
  actual_start_time: '',
  actual_end_time: ''
})
const usageRecordDetail = ref({})
const usageRecordSubmitLoading = ref(false)
const currentReservation = ref(null)

// 预约详情相关数据
const reservationDetailDialogVisible = ref(false)
const reservationDetail = ref({})

// 设备和试剂查看相关数据
const equipmentDialogVisible = ref(false)
const reagentDialogVisible = ref(false)
const currentLab = ref(null)
const equipmentList = ref([])
const reagentList = ref([])
const equipmentLoading = ref(false)
const reagentLoading = ref(false)

// 获取实验室列表（包含负责人详细信息）
const getList = async () => {
  try {
    loading.value = true
    const res = await getLabList({ include_manager_details: true }) // 请求包含负责人详细信息
    if (res.code === 0 && res.data && Array.isArray(res.data.list)) {
      // 显示所有实验室，让用户通过搜索来过滤
      labList.value = res.data.list
    } else {
      labList.value = []
      console.warn('实验室数据格式不正确')
    }
  } catch (error) {
    console.error('获取实验室列表失败：', error)
    ElMessage.error('获取实验室列表失败')
    labList.value = []
  } finally {
    loading.value = false
  }
}

// 获取我的预约
const getMyReservationsList = async () => {
  try {
    loading.value = true
    const res = await getMyReservations({
      page: myPage.value,
      limit: myLimit.value
    })
    if (res.code === 0 && res.data) {
      myReservations.value = res.data.list || []
      myTotal.value = res.data.total || 0
    } else {
      myReservations.value = []
      myTotal.value = 0
    }
  } catch (error) {
    console.error('获取我的预约失败：', error)
    ElMessage.error('获取我的预约失败')
    myReservations.value = []
    myTotal.value = 0
  } finally {
    loading.value = false
  }
}

// 获取所有预约（管理员和实验室负责人）
const getAllReservations = async () => {
  try {
    loading.value = true
    const params = {
      page: allPage.value,
      limit: allLimit.value,
      lab_id: searchForm.lab_id,
      status: searchForm.status
    }
    
    // 如果不是管理员，只能查看自己负责的实验室预约
    if (!isAdmin.value && userStore.userInfo) {
      const managedLabIds = labList.value
        .filter(lab => lab.manager_id === userStore.userInfo.id)
        .map(lab => lab.id)
      
      if (managedLabIds.length === 0) {
        // 如果不是任何实验室的负责人，返回空列表
        allReservations.value = []
        allTotal.value = 0
        loading.value = false
        return
      }
      
      // 如果没有指定实验室且有多个负责的实验室，需要传递负责的实验室列表
      if (!params.lab_id) {
        params.manager_labs = managedLabIds
      } else if (!managedLabIds.includes(parseInt(params.lab_id))) {
        // 如果指定了实验室但不是自己负责的，返回空列表
        allReservations.value = []
        allTotal.value = 0
        loading.value = false
        return
      }
    }
    
    if (searchForm.timeRange && searchForm.timeRange.length === 2) {
      params.start_time = searchForm.timeRange[0]
      params.end_time = searchForm.timeRange[1]
    }
    
    const res = await getReservationList(params)
    if (res.code === 0 && res.data) {
      let reservations = res.data.list || []
      
      // 如果API不支持manager_labs参数，在前端过滤
      if (!isAdmin.value && userStore.userInfo && params.manager_labs) {
        const managedLabIds = labList.value
          .filter(lab => lab.manager_id === userStore.userInfo.id)
          .map(lab => lab.id)
        reservations = reservations.filter(reservation => 
          managedLabIds.includes(reservation.lab_id)
        )
      }
      
      allReservations.value = reservations
      allTotal.value = res.data.total || 0
    } else {
      allReservations.value = []
      allTotal.value = 0
    }
  } catch (error) {
    console.error('获取预约列表失败：', error)
    ElMessage.error('获取预约列表失败')
    allReservations.value = []
    allTotal.value = 0
  } finally {
    loading.value = false
  }
}

// 打开预约页面
const handleReserve = (row) => {
  router.push({
    name: 'LabReservationCreate',
    query: {
      lab_id: row.id
    }
  })
}

// 取消预约
const handleCancel = async (row) => {
  try {
    await ElMessageBox.confirm('确定要取消该预约吗？', '提示', {
      type: 'warning'
    })
    const res = await cancelReservation(row.id)
    if (res.code === 0) {
      ElMessage.success('取消成功')
      getMyReservationsList()
      if (isAdmin.value) {
        getAllReservations()
      }
    } else {
      ElMessage.error(res.msg || '取消失败')
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('取消预约失败：', error)
      ElMessage.error('取消预约失败：' + (error.message || '未知错误'))
    }
  }
}

// 审核预约
const handleReview = async (row, status) => {
  try {
    // 前端权限检查
    if (!canReview(row)) {
      ElMessage.error('您没有权限审核该预约')
      return
    }
    
    const action = status === 'approved' ? '批准' : '拒绝'
    await ElMessageBox.confirm(`确定要${action}该预约吗？`, '提示', {
      type: 'warning'
    })
    const res = await reviewReservation({
      id: row.id,
      status
    })
    if (res.code === 0) {
      ElMessage.success(`${action}成功`)
      getAllReservations()
    } else {
      ElMessage.error(res.msg || `${action}失败`)
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('审核预约失败：', error)
      ElMessage.error('审核预约失败：' + (error.message || '未知错误'))
    }
  }
}

// 搜索
const handleSearch = () => {
  // 实验室列表搜索通过计算属性 filteredLabList 自动更新
  // 预约管理搜索
  if (activeTab.value === 'all') {
    allPage.value = 1
    getAllReservations()
  }
}

// 重置搜索
const handleReset = () => {
  // 重置实验室搜索表单
  searchForm.name = ''
  searchForm.location = ''
  searchForm.status = ''
}

// 重置预约管理搜索
const resetSearch = () => {
  searchForm.lab_id = ''
  searchForm.status = ''
  searchForm.timeRange = []
  handleSearch()
}

// 格式化日期时间
const formatDateTime = (datetime) => {
  return datetime ? new Date(datetime).toLocaleString() : ''
}

// 获取状态类型
const getStatusType = (status) => {
  const types = {
    pending: 'warning',
    approved: 'success',
    rejected: 'danger',
    completed: 'info',
    cancelled: 'info'
  }
  return types[status] || 'info'
}

// 获取状态文本
const getStatusText = (status) => {
  const texts = {
    pending: '待审核',
    approved: '已批准',
    rejected: '已拒绝',
    completed: '已完成',
    cancelled: '已取消'
  }
  return texts[status] || status
}

// 填写使用记录
const handleFillUsageRecord = (reservation) => {
  currentReservation.value = reservation
  usageRecordForm.reservation_id = reservation.id
  usageRecordForm.actual_start_time = reservation.start_time
  usageRecordForm.actual_end_time = reservation.end_time
  usageRecordDialogVisible.value = true
}

// 查看使用记录
const handleViewUsageRecord = async (reservation) => {
  try {
    const res = await getUsageRecord({ reservation_id: reservation.id })
    if (res.code === 0) {
      usageRecordDetail.value = res.data
      usageRecordViewDialogVisible.value = true
    } else {
      ElMessage.error(res.msg || '获取使用记录失败')
    }
  } catch (error) {
    console.error('获取使用记录失败：', error)
    ElMessage.error('获取使用记录失败')
  }
}

// 提交使用记录
const handleSubmitUsageRecord = async () => {
  try {
    usageRecordSubmitLoading.value = true
    
    const submitData = {
      reservation_id: usageRecordForm.reservation_id,
      power_off: usageRecordForm.power_off,
      air_conditioning_off: usageRecordForm.air_conditioning_off,
      hygiene_completed: usageRecordForm.hygiene_completed,
      equipment_normal: usageRecordForm.equipment_normal,
      equipment_issues: usageRecordForm.equipment_issues,
      other_notes: usageRecordForm.other_notes,
      actual_start_time: usageRecordForm.actual_start_time,
      actual_end_time: usageRecordForm.actual_end_time
    }
    
    const res = await createUsageRecord(submitData)
    if (res.code === 0) {
      ElMessage.success('使用记录提交成功')
      usageRecordDialogVisible.value = false
      resetUsageRecordForm()
      // 刷新我的预约列表
      getMyReservationsList()
    } else {
      ElMessage.error(res.msg || '提交失败')
    }
  } catch (error) {
    console.error('提交使用记录失败：', error)
    ElMessage.error('提交使用记录失败')
  } finally {
    usageRecordSubmitLoading.value = false
  }
}

// 重置使用记录表单
const resetUsageRecordForm = () => {
  Object.assign(usageRecordForm, {
    reservation_id: '',
    power_off: 1,
    air_conditioning_off: 1,
    hygiene_completed: 1,
    equipment_normal: 1,
    equipment_issues: '',
    other_notes: '',
    actual_start_time: '',
    actual_end_time: ''
  })
  currentReservation.value = null
}

// 查看设备
const handleViewEquipment = async (lab) => {
  currentLab.value = lab
  equipmentDialogVisible.value = true
  await fetchEquipmentList(lab.id)
}

// 查看试剂
const handleViewReagent = async (lab) => {
  currentLab.value = lab
  reagentDialogVisible.value = true
  await fetchReagentList(lab.id)
}

// 获取设备列表
const fetchEquipmentList = async (labId) => {
  equipmentLoading.value = true
  try {
    const res = await getEquipmentList({
      lab_id: labId,
      limit: 1000 // 获取所有设备
    })
    if (res.code === 0) {
      equipmentList.value = res.data.list || []
    } else {
      ElMessage.error(res.msg || '获取设备列表失败')
      equipmentList.value = []
    }
  } catch (error) {
    console.error('获取设备列表失败:', error)
    ElMessage.error('获取设备列表失败')
    equipmentList.value = []
  } finally {
    equipmentLoading.value = false
  }
}

// 获取试剂列表
const fetchReagentList = async (labId) => {
  reagentLoading.value = true
  try {
    const res = await getReagentList({
      lab_id: labId,
      limit: 1000 // 获取所有试剂
    })
    if (res.code === 0) {
      reagentList.value = res.data.list || []
    } else {
      ElMessage.error(res.msg || '获取试剂列表失败')
      reagentList.value = []
    }
  } catch (error) {
    console.error('获取试剂列表失败:', error)
    ElMessage.error('获取试剂列表失败')
    reagentList.value = []
  } finally {
    reagentLoading.value = false
  }
}

// 设备状态文本
const getEquipmentStatusText = (status) => {
  const statusMap = {
    normal: '正常',
    maintenance: '维修中',
    damaged: '故障',
    scrapped: '报废'
  }
  return statusMap[status] || status
}

// 危险等级类型
const getDangerLevelType = (level) => {
  const types = {
    low: 'success',
    medium: 'warning',
    high: 'danger'
  }
  return types[level] || 'info'
}

// 危险等级文本
const getDangerLevelText = (level) => {
  const texts = {
    low: '低危',
    medium: '中危',
    high: '高危'
  }
  return texts[level] || level
}

// 库存状态样式
const getStockClass = (current, min) => {
  if (current <= 0) return 'text-red-500 font-bold'
  if (current <= min) return 'text-orange-500 font-semibold'
  return 'text-green-600'
}

// 有效期状态样式
const getExpiryClass = (expiryDate) => {
  if (!expiryDate) return ''
  const today = new Date()
  const expiry = new Date(expiryDate)
  const diffDays = Math.ceil((expiry - today) / (1000 * 60 * 60 * 24))
  
  if (diffDays < 0) return 'text-red-500 font-bold' // 已过期
  if (diffDays <= 30) return 'text-orange-500 font-semibold' // 30天内过期
  if (diffDays <= 90) return 'text-yellow-600' // 90天内过期
  return 'text-green-600' // 正常
}

// 查看预约详情
const handleViewReservationDetail = (reservation) => {
  reservationDetail.value = {
    ...reservation,
    manager_info: getLabManager(reservation.lab_id)
  }
  reservationDetailDialogVisible.value = true
}

// 监听标签页切换
watch(activeTab, (newVal) => {
  if (newVal === 'my') {
    getMyReservationsList()
  } else if (newVal === 'all' && (isAdmin.value || hasManagerLabs.value)) {
    getAllReservations()
  }
})

onMounted(() => {
  getList()
  if (activeTab.value === 'my') {
    getMyReservationsList()
  } else if (activeTab.value === 'all' && (isAdmin.value || hasManagerLabs.value)) {
    getAllReservations()
  }
})
</script>

<style scoped>
.manager-info {
  display: flex;
  flex-direction: column;
  gap: 8px;
  min-height: 40px;
}

.manager-name {
  font-weight: 500;
  color: #303133;
  font-size: 14px;
  line-height: 1.4;
}

.manager-contact {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.contact-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: #606266;
  cursor: pointer;
  padding: 2px 0;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.contact-item:hover {
  background-color: #f5f7fa;
  color: #409eff;
  transform: translateY(-1px);
  box-shadow: 0 2px 6px rgba(64, 158, 255, 0.2);
}

.contact-icon {
  font-size: 14px;
  color: #909399;
  flex-shrink: 0;
}

.contact-item:hover .contact-icon {
  color: #409eff;
}

.contact-item span {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  flex: 1;
  min-width: 0;
}

/* 鼠标悬停时的提示样式 */
.contact-item[title] {
  cursor: help;
}

/* 可点击的联系方式样式 */
.contact-item {
  cursor: pointer;
  user-select: none;
}

.contact-item:active {
  transform: translateY(0);
  box-shadow: 0 1px 3px rgba(64, 158, 255, 0.3);
}

/* 空状态样式 */
.text-gray-400 {
  color: #c0c4cc;
  font-size: 12px;
  font-style: italic;
}

/* 预约卡片网格布局 */
.reservations-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 20px;
  margin-bottom: 24px;
}

/* 预约卡片样式 */
.reservation-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
  border-left: 4px solid transparent;
}

.reservation-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

/* 管理员卡片特殊样式 */
.reservation-card.admin {
  border-left-color: #f39c12;
}

.reservation-card:not(.admin) {
  border-left-color: #409eff;
}

/* 预约卡片头部 */
.reservation-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}

.reservation-status .el-tag {
  font-weight: 600;
  border-radius: 8px;
  padding: 8px 16px;
}

.reservation-time {
  display: flex;
  align-items: center;
  font-size: 12px;
  color: #7f8c8d;
  font-family: 'Monaco', 'Consolas', monospace;
}

.reservation-priority {
  margin-left: 8px;
}

/* 预约内容区域 */
.reservation-content {
  margin-bottom: 20px;
}

.lab-and-user {
  margin-bottom: 16px;
}

.lab-name {
  font-size: 18px;
  font-weight: 600;
  color: #2c3e50;
  margin: 0 0 8px 0;
}

.user-info {
  display: flex;
  align-items: center;
  font-size: 14px;
  color: #7f8c8d;
}

/* 时间信息 */
.time-info {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
  padding: 12px;
  background: #f8f9fa;
  border-radius: 8px;
  flex-wrap: wrap;
}

.time-item {
  display: flex;
  align-items: center;
  font-size: 14px;
  color: #2c3e50;
  font-family: 'Monaco', 'Consolas', monospace;
}

.time-separator {
  color: #95a5a6;
  font-weight: 500;
  margin: 0 4px;
}

/* 用途信息 */
.purpose-info {
  margin-bottom: 16px;
}

.purpose-label {
  display: flex;
  align-items: center;
  font-size: 13px;
  color: #7f8c8d;
  margin-bottom: 8px;
  font-weight: 500;
}

.purpose-text {
  background: #f8f9fa;
  padding: 12px;
  border-radius: 8px;
  color: #2c3e50;
  line-height: 1.5;
  word-break: break-word;
}

/* 负责人区域 */
.manager-section {
  margin-bottom: 16px;
  padding: 16px;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-radius: 8px;
}

.manager-section h4 {
  margin: 0 0 12px 0;
  font-size: 14px;
  color: #495057;
  font-weight: 600;
}

.manager-section .manager-info {
  gap: 12px;
}

.manager-section .manager-name {
  display: flex;
  align-items: center;
  font-size: 15px;
  font-weight: 600;
  color: #2c3e50;
}

.manager-contacts {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}

.manager-section .contact-item {
  background: white;
  padding: 8px 12px;
  border-radius: 6px;
  border: 1px solid #e9ecef;
  transition: all 0.2s ease;
}

.manager-section .contact-item:hover {
  border-color: #409eff;
  background: #f0f9ff;
  transform: translateY(-2px);
}

.no-manager {
  padding: 16px;
  text-align: center;
  color: #95a5a6;
  font-style: italic;
  background: #f8f9fa;
  border-radius: 8px;
  margin-bottom: 16px;
}

/* 操作按钮区域 */
.reservation-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  align-items: center;
}

.review-actions {
  display: flex;
  gap: 8px;
  flex: 1;
}

.approve-btn {
  background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
  border: none;
  border-radius: 8px;
  padding: 10px 20px;
  font-weight: 500;
}

.reject-btn {
  background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
  border: none;
  border-radius: 8px;
  padding: 10px 20px;
  font-weight: 500;
}

.cancel-btn {
  background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
  border: none;
  border-radius: 8px;
  padding: 10px 20px;
  font-weight: 500;
}

.detail-btn {
  border-radius: 8px;
  padding: 8px 16px;
}

.no-permission {
  flex: 1;
}

/* 搜索表单优化 */
.search-date {
  width: 300px;
}

/* 分页样式 */
.pagination-wrapper {
  display: flex;
  justify-content: center;
  margin-top: 24px;
  padding: 20px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
}

/* 状态标签增强样式 */
.status-pending {
  background: linear-gradient(135deg, #f39c12 20%, #e67e22 100%);
  border: none;
  color: white;
}

.status-approved {
  background: linear-gradient(135deg, #27ae60 20%, #2ecc71 100%);
  border: none;
  color: white;
}

.status-rejected {
  background: linear-gradient(135deg, #e74c3c 20%, #c0392b 100%);
  border: none;
  color: white;
}

.status-completed {
  background: linear-gradient(135deg, #95a5a6 20%, #7f8c8d 100%);
  border: none;
  color: white;
}

.status-cancelled {
  background: linear-gradient(135deg, #95a5a6 20%, #7f8c8d 100%);
  border: none;
  color: white;
}

/* 空状态样式 */
.empty-state {
  grid-column: 1 / -1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
}

.empty-icon {
  margin-bottom: 24px;
  opacity: 0.6;
}

.empty-text {
  text-align: center;
}

.empty-text h3 {
  color: #2c3e50;
  margin: 0 0 16px 0;
  font-size: 18px;
  font-weight: 600;
}

.empty-text p {
  color: #7f8c8d;
  margin: 0;
  line-height: 1.6;
}

.usage-btn {
  background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
  border: none;
  border-radius: 8px;
  padding: 10px 20px;
  font-weight: 500;
}

.view-usage-btn {
  border-color: #409eff;
  color: #409eff;
  border-radius: 8px;
}

.view-usage-btn:hover {
  background: #409eff;
  color: white;
}

/* 使用记录对话框样式 */
.usage-record-form {
  padding: 10px 0;
}

.reservation-info {
  background: #f8f9fa;
  padding: 16px;
  border-radius: 8px;
  margin-bottom: 24px;
}

.reservation-info h4 {
  margin: 0 0 12px 0;
  color: #2c3e50;
  font-size: 16px;
  font-weight: 600;
}

.info-item {
  margin-bottom: 8px;
  display: flex;
  align-items: center;
}

.info-item .label {
  font-weight: 500;
  color: #606266;
  margin-right: 8px;
  min-width: 80px;
}

.form-section {
  margin-bottom: 24px;
}

.form-section h4 {
  margin: 0 0 16px 0;
  color: #2c3e50;
  font-size: 16px;
  font-weight: 600;
  padding-bottom: 8px;
  border-bottom: 2px solid #ecf0f1;
}

.usage-record-detail {
  padding: 10px 0;
}

.detail-section {
  margin-bottom: 24px;
}

.detail-section h4 {
  color: #2c3e50;
  margin: 0 0 16px 0;
  font-size: 16px;
  font-weight: 600;
  padding-bottom: 8px;
  border-bottom: 2px solid #ecf0f1;
}

.content-text {
  background: #f8f9fa;
  padding: 16px;
  border-radius: 8px;
  line-height: 1.6;
  color: #2c3e50;
}

/* 预约详情对话框样式 */
.reservation-detail {
  padding: 10px 0;
}

.reservation-detail .detail-section {
  margin-bottom: 24px;
}

.reservation-detail .detail-section h4 {
  color: #2c3e50;
  margin: 0 0 16px 0;
  font-size: 16px;
  font-weight: 600;
  padding-bottom: 8px;
  border-bottom: 2px solid #ecf0f1;
}

.reservation-detail .contact-item {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 4px;
  transition: all 0.2s ease;
  background: #f8f9fa;
}

.reservation-detail .contact-item:hover {
  background: #e9ecef;
  color: #409eff;
}

.action-section {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

/* 响应式设计 */
@media (max-width: 1024px) {
  .reservations-grid {
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 16px;
  }
  
  .labs-grid {
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 16px;
  }
  
  .search-form {
    flex-direction: column;
    align-items: stretch;
  }
  
  .search-input, .search-select, .search-date {
    width: 100%;
  }
}

@media (max-width: 768px) {
  .reservation-management {
    padding: 16px;
  }
  
  .page-header {
    padding: 16px;
  }
  
  .header-content {
    flex-direction: column;
    gap: 16px;
    text-align: center;
  }
  
  .reservations-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .labs-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .reservation-card {
    padding: 16px;
  }
  
  .lab-card {
    padding: 16px;
  }
  
  .time-info {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .time-separator {
    margin: 0;
    align-self: center;
  }
  
  .manager-contacts {
    flex-direction: column;
    gap: 8px;
  }
  
  .reservation-actions {
    flex-direction: column;
  }
  
  .review-actions {
    flex-direction: column;
    gap: 8px;
  }
  
  .search-form {
    gap: 12px;
  }
  
  .pagination-wrapper {
    padding: 16px;
  }
}

@media (max-width: 480px) {
  .page-title {
    font-size: 24px;
  }
  
  .lab-name {
    font-size: 16px;
  }
  
  .reservation-card {
    padding: 12px;
  }
  
  .lab-card {
    padding: 12px;
  }
  
  .action-buttons {
    gap: 8px;
  }
  
  .view-buttons {
    flex-direction: column;
    gap: 6px;
  }
  
  .view-btn {
    font-size: 11px;
    padding: 6px 10px;
  }
  
  .manager-section {
    padding: 12px;
  }
  
  .lab-manager {
    padding: 12px;
  }
}

/* 确保表格内容不会过度拉伸 */
:deep(.el-table .el-table__cell) {
  padding: 8px 0;
}

:deep(.el-table .manager-info) {
  max-width: 200px;
}

/* 整体布局样式 */
.reservation-management {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  padding: 24px;
}

/* 页面头部样式 */
.page-header {
  background: white;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 24px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.title-section {
  display: flex;
  align-items: center;
  gap: 16px;
}

.page-title {
  font-size: 28px;
  font-weight: 600;
  color: #2c3e50;
  margin: 0;
}

.page-subtitle {
  color: #7f8c8d;
  margin: 4px 0 0 0;
  font-size: 14px;
}

.tab-switch {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* 搜索卡片样式 */
.search-card {
  border-radius: 12px;
  margin-bottom: 24px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
  border: none;
}

.card-header {
  display: flex;
  align-items: center;
  font-weight: 600;
  color: #2c3e50;
}

.search-form {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  align-items: center;
}

.search-input, .search-select {
  width: 200px;
}

.search-btn {
  background: linear-gradient(135deg, #409eff 0%, #3a8ee6 100%);
  border: none;
  border-radius: 8px;
  font-weight: 500;
}

.reset-btn {
  border-radius: 8px;
}

/* 实验室卡片网格布局 */
.labs-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
  gap: 20px;
  margin-bottom: 24px;
}

/* 实验室卡片样式 */
.lab-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
  border-left: 4px solid #409eff;
}

.lab-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.lab-card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}

.lab-status .el-tag {
  font-weight: 600;
  border-radius: 8px;
  padding: 6px 12px;
}

.lab-capacity {
  display: flex;
  align-items: center;
  font-size: 14px;
  color: #7f8c8d;
  background: #f8f9fa;
  padding: 6px 12px;
  border-radius: 6px;
}

.lab-info {
  margin-bottom: 20px;
}

.lab-name {
  font-size: 18px;
  font-weight: 600;
  color: #2c3e50;
  margin: 0 0 8px 0;
}

.lab-location {
  display: flex;
  align-items: center;
  font-size: 14px;
  color: #7f8c8d;
}

.lab-manager {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  padding: 16px;
  border-radius: 8px;
  margin-bottom: 16px;
}

.lab-manager h4 {
  margin: 0 0 12px 0;
  font-size: 14px;
  color: #495057;
  font-weight: 600;
}

.manager-info {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.manager-name {
  display: flex;
  align-items: center;
  font-size: 15px;
  font-weight: 600;
  color: #2c3e50;
}

.manager-contacts {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}

.lab-manager .contact-item {
  background: white;
  padding: 8px 12px;
  border-radius: 6px;
  border: 1px solid #e9ecef;
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  color: #606266;
  cursor: pointer;
  transition: all 0.2s ease;
}

.lab-manager .contact-item:hover {
  border-color: #409eff;
  background: #f0f9ff;
  color: #409eff;
  transform: translateY(-2px);
}

.no-manager {
  text-align: center;
  color: #95a5a6;
  font-style: italic;
  background: #f8f9fa;
  padding: 16px;
  border-radius: 8px;
  margin-bottom: 16px;
}

.lab-actions {
  display: flex;
  justify-content: center;
}

.action-buttons {
  display: flex;
  flex-direction: column;
  gap: 12px;
  width: 100%;
}

.view-buttons {
  display: flex;
  gap: 8px;
  justify-content: center;
}

.reserve-btn {
  width: 100%;
  background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
  border: none;
  border-radius: 8px;
  padding: 12px 24px;
  font-weight: 500;
  font-size: 14px;
}

.view-btn {
  flex: 1;
  border-radius: 6px;
  font-size: 12px;
  padding: 8px 12px;
  transition: all 0.3s ease;
}

.view-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.reserve-btn:disabled {
  background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
  color: white;
}

/* 状态样式 */
.status-0 {
  background: linear-gradient(135deg, #27ae60 20%, #2ecc71 100%);
  border: none;
  color: white;
}

.status-1 {
  background: linear-gradient(135deg, #e74c3c 20%, #c0392b 100%);
  border: none;
  color: white;
}

/* 设备和试剂列表样式 */
.equipment-list, .reagent-list {
  padding: 16px 0;
}

.equipment-list .el-table, .reagent-list .el-table {
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
}

.equipment-list .el-table__header, .reagent-list .el-table__header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.equipment-list .el-table__header th, .reagent-list .el-table__header th {
  background: transparent;
  color: white;
  font-weight: 600;
  border: none;
}

.equipment-list .el-table__body tr:hover, .reagent-list .el-table__body tr:hover {
  background: linear-gradient(135deg, #f8f9ff 0%, #f3f0ff 100%);
}

/* 颜色工具类 */
.text-red-500 {
  color: #ef4444;
}

.text-orange-500 {
  color: #f97316;
}

.text-yellow-600 {
  color: #ca8a04;
}

.text-green-600 {
  color: #16a34a;
}

.text-gray-400 {
  color: #9ca3af;
}

.font-bold {
  font-weight: 700;
}

.font-semibold {
  font-weight: 600;
}

/* 空状态样式优化 */
.equipment-list .empty-state, .reagent-list .empty-state {
  text-align: center;
  padding: 40px 20px;
  background: #fafafa;
  border-radius: 8px;
  margin: 16px 0;
}

.equipment-list .empty-icon, .reagent-list .empty-icon {
  margin-bottom: 16px;
  opacity: 0.6;
}

.equipment-list .empty-text p, .reagent-list .empty-text p {
  color: #7f8c8d;
  margin: 0;
  font-size: 14px;
}
</style> 