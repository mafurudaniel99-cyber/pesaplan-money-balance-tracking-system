-- =====================================================================
-- PesaPlan — Seed Data
-- Default expense categories (user_id = NULL means system default
-- shared by all users). Run AFTER schema.sql.
-- =====================================================================

SET NAMES utf8mb4;

INSERT INTO `expense_categories`
  (`user_id`,`name`,`type`,`icon`,`color`,`kind`,`is_active`,`sort_order`)
VALUES
  (NULL, 'Chakula',                       'expense', 'fa-utensils',       '#e07a5f', 'essential',    1,  1),
  (NULL, 'Kodi ya nyumba',                'expense', 'fa-house',          '#3d5a80', 'essential',    1,  2),
  (NULL, 'Usafiri',                       'expense', 'fa-bus',            '#2980b9', 'essential',    1,  3),
  (NULL, 'Umeme na maji',                 'expense', 'fa-bolt',           '#f39c12', 'essential',    1,  4),
  (NULL, 'Mawasiliano na intaneti',       'expense', 'fa-mobile-screen',  '#16a085', 'essential',    1,  5),
  (NULL, 'Elimu',                         'expense', 'fa-book',           '#8e44ad', 'essential',    1,  6),
  (NULL, 'Afya',                          'expense', 'fa-heart-pulse',    '#c0392b', 'essential',    1,  7),
  (NULL, 'Familia',                       'expense', 'fa-people-roof',    '#27ae60', 'essential',    1,  8),
  (NULL, 'Madeni',                        'debt',    'fa-hand-holding-dollar', '#7f8c8d', 'obligation', 1, 9),
  (NULL, 'Akiba',                         'savings', 'fa-piggy-bank',     '#16a085', 'savings',      1, 10),
  (NULL, 'Maendeleo binafsi',             'expense', 'fa-seedling',       '#2c3e50', 'other',        1, 11),
  (NULL, 'Starehe na burudani',           'expense', 'fa-music',          '#d35400', 'discretionary',1, 12),
  (NULL, 'Manunuzi binafsi',              'expense', 'fa-bag-shopping',   '#8e44ad', 'discretionary',1, 13),
  (NULL, 'Dharura',                       'expense', 'fa-triangle-exclamation', '#c0392b', 'essential', 1, 14),
  (NULL, 'Nyinginezo',                    'expense', 'fa-ellipsis',       '#7f8c8d', 'other',        1, 99),
  (NULL, 'Mshahara',                      'income',  'fa-money-bill-trend-up', '#27ae60', 'other',    1,  1),
  (NULL, 'Mapato mengine',                'income',  'fa-sack-dollar',    '#16a085', 'other',        1,  2);
