/*
 Navicat Premium Data Transfer

 Source Server         : local-mysql
 Source Server Type    : MySQL
 Source Server Version : 80404 (8.4.4)
 Source Host           : localhost:3306
 Source Schema         : symfony_aio

 Target Server Type    : MySQL
 Target Server Version : 80404 (8.4.4)
 File Encoding         : 65001

 Date: 27/05/2025 06:22:24
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for job_training_category
-- ----------------------------
DROP TABLE IF EXISTS `job_training_category`;
CREATE TABLE `job_training_category` (
  `id` bigint NOT NULL COMMENT 'ID',
  `parent_id` bigint DEFAULT NULL COMMENT 'ID',
  `create_user_id` int DEFAULT NULL COMMENT 'ID',
  `update_user_id` int DEFAULT NULL COMMENT 'ID',
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '分类名称',
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '创建人',
  `updated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '更新人',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `sort_number` int DEFAULT '0' COMMENT '次序值',
  PRIMARY KEY (`id`),
  KEY `IDX_E52FCAA3727ACA70` (`parent_id`),
  KEY `IDX_E52FCAA385564492` (`create_user_id`),
  KEY `IDX_E52FCAA3E0DFCA6C` (`update_user_id`),
  KEY `job_training_category_idx_create_time` (`create_time`),
  KEY `job_training_category_idx_sort_number` (`sort_number`),
  CONSTRAINT `FK_E52FCAA3727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `job_training_category` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_E52FCAA385564492` FOREIGN KEY (`create_user_id`) REFERENCES `biz_user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_E52FCAA3E0DFCA6C` FOREIGN KEY (`update_user_id`) REFERENCES `biz_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='资源分类';

-- ----------------------------
-- Records of job_training_category
-- ----------------------------
BEGIN;
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (4, NULL, NULL, NULL, '主要负责人', NULL, NULL, '2024-03-09 15:07:45', NULL, 0);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (5, NULL, NULL, NULL, '特种作业人员', NULL, NULL, '2024-03-09 15:07:45', NULL, 0);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (51, 5, NULL, NULL, '危险化学品安全作业', NULL, NULL, '2024-03-09 15:07:45', NULL, 7);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (52, 5, NULL, NULL, '制冷与空调作业', NULL, NULL, '2024-03-09 15:07:45', NULL, 4);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (53, 5, NULL, NULL, '焊接与热切割作业', NULL, NULL, '2024-03-09 15:07:45', NULL, 2);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (54, 5, NULL, NULL, '高处作业', NULL, NULL, '2024-03-09 15:07:45', NULL, 3);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (55, 5, NULL, NULL, '电工作业', NULL, NULL, '2024-03-09 15:07:45', NULL, 1);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (57, 55, NULL, NULL, '电气试验作业', NULL, NULL, '2024-03-09 15:07:45', NULL, 5);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (58, 55, NULL, NULL, '电力电缆作业', NULL, NULL, '2024-03-09 15:07:45', NULL, 3);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (59, 55, NULL, NULL, '防爆电气作业', NULL, NULL, '2024-03-09 15:07:45', NULL, 6);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (62, 54, NULL, NULL, '高处安装、维护、拆除作业', NULL, NULL, '2024-03-09 15:07:45', NULL, 2);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (63, 54, NULL, NULL, '登高架设作业', NULL, NULL, '2024-03-09 15:07:45', NULL, 1);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (64, 53, NULL, NULL, '熔化焊接与热切割作业', NULL, NULL, '2024-03-09 15:07:45', NULL, 1);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (65, 53, NULL, NULL, '钎焊作业', NULL, NULL, '2024-03-09 15:07:45', NULL, 3);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (66, 52, NULL, NULL, '制冷与空调设备安装修理作业', NULL, NULL, '2024-03-09 15:07:45', NULL, 2);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (67, 52, NULL, NULL, '制冷与空调设备运行操作作业', NULL, NULL, '2024-03-09 15:07:45', NULL, 1);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (79, 51, NULL, NULL, '光气及光气化工艺', NULL, NULL, '2024-03-09 15:07:45', NULL, 1);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (80, 5, NULL, NULL, '金属非金属矿山安全作业', NULL, NULL, '2024-03-09 15:07:45', NULL, 5);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (81, 55, NULL, NULL, '低压电工作业', NULL, NULL, '2024-03-09 15:07:45', NULL, 1);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (82, 55, NULL, NULL, '高压电工作业', NULL, NULL, '2024-03-09 15:07:45', NULL, 2);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (83, 5, NULL, NULL, '冶金(有色)生产安全作业', NULL, NULL, '2024-03-09 15:07:45', NULL, 7);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (84, 55, NULL, NULL, '继电保护作业', NULL, NULL, '2024-03-09 15:07:45', NULL, 4);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (85, 53, NULL, NULL, '压力焊作业', NULL, NULL, '2024-03-09 15:07:45', NULL, 2);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (86, 80, NULL, NULL, '金属非金属矿井通风作业', NULL, NULL, '2024-03-09 15:07:45', NULL, 1);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (87, 80, NULL, NULL, '尾矿作业', NULL, NULL, '2024-03-09 15:07:45', NULL, 2);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (88, 80, NULL, NULL, '安全检查作业(露天矿山)', NULL, NULL, '2024-03-09 15:07:45', NULL, 3);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (90, 83, NULL, NULL, '煤气作业', NULL, NULL, '2024-03-09 15:07:45', NULL, 0);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (91, 80, NULL, NULL, '安全检查作业(小型露天采石场)', NULL, NULL, '2024-03-09 15:07:45', NULL, 4);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (92, 80, NULL, NULL, '安全检查作业(地下矿山)', NULL, NULL, '2024-03-09 15:07:45', NULL, 5);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (93, 80, NULL, NULL, '提升机操作作业', NULL, NULL, '2024-03-09 15:07:45', NULL, 6);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (94, 80, NULL, NULL, '支柱作业', NULL, NULL, '2024-03-09 15:07:45', NULL, 7);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (95, 80, NULL, NULL, '井下电气作业', NULL, NULL, '2024-03-09 15:07:45', NULL, 8);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (96, 80, NULL, NULL, '排水作业', NULL, NULL, '2024-03-09 15:07:45', NULL, 9);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (97, 80, NULL, NULL, '爆破作业', NULL, NULL, '2024-03-09 15:07:45', NULL, 10);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (98, 51, NULL, NULL, '氯碱电解工艺', NULL, NULL, '2024-03-09 15:07:45', NULL, 2);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (99, 51, NULL, NULL, '氯化工艺', NULL, NULL, '2024-03-09 15:07:45', NULL, 3);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (100, 51, NULL, NULL, '硝化工艺', NULL, NULL, '2024-03-09 15:07:45', NULL, 4);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (101, 51, NULL, NULL, '合成氨工艺', NULL, NULL, '2024-03-09 15:07:45', NULL, 5);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (102, 51, NULL, NULL, '裂解(裂化)工艺', NULL, NULL, '2024-03-09 15:07:45', NULL, 6);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (103, 51, NULL, NULL, '氟化工艺', NULL, NULL, '2024-03-09 15:07:45', NULL, 7);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (104, 51, NULL, NULL, '加氢工艺', NULL, NULL, '2024-03-09 15:07:45', NULL, 8);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (105, 51, NULL, NULL, '重氮化工艺', NULL, NULL, '2024-03-09 15:07:45', NULL, 9);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (106, 51, NULL, NULL, '氧化工艺', NULL, NULL, '2024-03-09 15:07:45', NULL, 10);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (107, 51, NULL, NULL, '过氧化工艺', NULL, NULL, '2024-03-09 15:07:45', NULL, 11);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (108, 51, NULL, NULL, '胺基化工艺', NULL, NULL, '2024-03-09 15:07:45', NULL, 12);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (109, 51, NULL, NULL, '磺化工艺', NULL, NULL, '2024-03-09 15:07:45', NULL, 13);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (110, 51, NULL, NULL, '聚合工艺', NULL, NULL, '2024-03-09 15:07:45', NULL, 14);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (111, 51, NULL, NULL, '烷基化工艺', NULL, NULL, '2024-03-09 15:07:45', NULL, 15);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (112, 51, NULL, NULL, '化工自动化控制仪表', NULL, NULL, '2024-03-09 15:07:45', NULL, 16);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (113, 5, NULL, NULL, '烟花爆竹安全作业', NULL, NULL, '2024-03-09 15:07:45', NULL, 8);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (114, 113, NULL, NULL, '烟花爆竹储存作业', NULL, NULL, '2024-03-09 15:07:45', NULL, 0);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (115, 4, NULL, NULL, '金属非金属矿山', NULL, NULL, '2024-03-09 15:07:45', NULL, 2);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (116, 4, NULL, NULL, '危险化学品', NULL, NULL, '2024-03-09 15:07:45', NULL, 1);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (117, 4, NULL, NULL, '金属冶炼', NULL, NULL, '2024-03-09 15:07:45', NULL, 5);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (118, 115, NULL, NULL, '小型露天采石场', NULL, NULL, '2024-03-09 15:07:45', NULL, 1);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (119, 115, NULL, NULL, '露天矿山', NULL, NULL, '2024-03-09 15:07:45', NULL, 3);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (120, 115, NULL, NULL, '地下矿山', NULL, NULL, '2024-03-09 15:07:45', NULL, 5);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (121, 116, NULL, NULL, '生产单位主要负责人', NULL, NULL, '2024-03-09 15:07:45', NULL, 1);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (122, 116, NULL, NULL, '经营单位主要负责人', NULL, NULL, '2024-03-09 15:07:45', NULL, 3);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (135, 145, NULL, NULL, '小型露天采石场', NULL, NULL, '2024-03-09 15:07:45', NULL, 2);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (136, 145, NULL, NULL, '露天矿山', NULL, NULL, '2024-03-09 15:07:45', NULL, 4);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (137, 145, NULL, NULL, '地下矿山', NULL, NULL, '2024-03-09 15:07:45', NULL, 6);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (138, 146, NULL, NULL, '生产单位安全管理人员', NULL, NULL, '2024-03-09 15:07:45', NULL, 2);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (139, 146, NULL, NULL, '经营单位安全管理人员', NULL, NULL, '2024-03-09 15:07:45', NULL, 4);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (140, 170, NULL, NULL, '烟花爆竹经营单位主要负责人', NULL, NULL, '2024-03-09 15:07:45', NULL, 7);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (144, NULL, NULL, NULL, '安全生产管理人员', NULL, NULL, '2024-03-09 15:07:45', NULL, 0);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (145, 144, NULL, NULL, '金属非金属矿山', NULL, NULL, '2024-03-09 15:07:45', NULL, 2);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (146, 144, NULL, NULL, '危险化学品', NULL, NULL, '2024-03-09 15:07:45', NULL, 1);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (147, 144, NULL, NULL, '金属冶炼', NULL, NULL, '2024-03-09 15:07:45', NULL, 5);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (160, 174, NULL, NULL, '烟花爆竹经营单位安全生产管理人员', NULL, NULL, '2024-03-09 15:07:45', NULL, 7);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (161, 4, NULL, NULL, '非高危企业', NULL, NULL, '2024-03-09 15:07:45', NULL, 6);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (162, 161, NULL, NULL, '非高危企业生产经营单位', NULL, NULL, '2024-03-09 15:07:45', NULL, 0);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (163, 144, NULL, NULL, '非高危企业', NULL, NULL, '2024-03-09 15:07:45', NULL, 6);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (164, 163, NULL, NULL, '非高危企业生产经营单位', NULL, NULL, '2024-03-09 15:07:45', NULL, 0);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (166, 5, NULL, NULL, '石油天然气安全作业', NULL, NULL, '2024-03-09 15:07:45', NULL, 6);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (168, 166, NULL, NULL, '司钻作业（钻井作业）', NULL, NULL, '2024-03-09 15:07:45', NULL, 1);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (169, 166, NULL, NULL, '司钻作业（井下作业）', NULL, NULL, '2024-03-09 15:07:45', NULL, 2);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (170, 4, NULL, NULL, '烟花爆竹', NULL, NULL, '2024-03-09 15:07:45', NULL, 4);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (171, 4, NULL, NULL, '石油天然气开采', NULL, NULL, '2024-03-09 15:07:45', NULL, 3);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (172, 171, NULL, NULL, '陆上石油天然气开采主要负责人', NULL, NULL, '2024-03-09 15:07:45', NULL, 0);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (173, NULL, NULL, 1, '石油天然气开采', NULL, 'admin', '2024-03-09 15:07:45', '2024-12-19 10:56:53', 3);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (174, 144, NULL, NULL, '烟花爆竹', NULL, NULL, '2024-03-09 15:07:45', NULL, 4);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (175, 173, NULL, NULL, '陆上石油天然气开采安全生产管理人员', NULL, NULL, '2024-03-09 15:07:45', NULL, 0);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (176, 117, NULL, NULL, '金属冶炼单位主要负责人安全生产', NULL, NULL, '2024-03-09 15:07:45', NULL, 0);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (177, 147, NULL, NULL, '金属冶炼单位安全管理人员', NULL, NULL, '2024-03-09 15:07:45', NULL, 0);
INSERT INTO `job_training_category` (`id`, `parent_id`, `create_user_id`, `update_user_id`, `title`, `created_by`, `updated_by`, `create_time`, `update_time`, `sort_number`) VALUES (618489900002422784, NULL, NULL, NULL, '未分类', NULL, NULL, '2024-04-10 01:06:11', NULL, 0);
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
