CREATE TABLE `cars`
(
    `id`          bigint UNSIGNED NOT NULL,
    `source_id`   bigint UNSIGNED NOT NULL,
    `name`        json                                    DEFAULT NULL,
    `model`       varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `category`    json                                    DEFAULT NULL,
    `year`        varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `state`       json                                    DEFAULT NULL,
    `full_weight` json                                    DEFAULT NULL,
    `weight`      bigint                                  DEFAULT NULL,
    `fuel`        json                                    DEFAULT NULL,
    `origin`      json                                    DEFAULT NULL,
    `created_at`  timestamp       NULL                    DEFAULT NULL,
    `updated_at`  timestamp       NULL                    DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

ALTER TABLE `cars`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `cars`
    MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 1;

CREATE TABLE `images`
(
    `id`     bigint UNSIGNED                         NOT NULL,
    `car_id` bigint UNSIGNED                         NOT NULL,
    `path`   varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `order`  int                                     NOT NULL DEFAULT '0',
    `type`   varchar(255) COLLATE utf8mb4_unicode_ci          DEFAULT NULL,
    `size`   varchar(255) COLLATE utf8mb4_unicode_ci          DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

ALTER TABLE `images`
    ADD PRIMARY KEY (`id`),
    ADD KEY `images_car_id_foreign` (`car_id`);

ALTER TABLE `images`
    MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 1;

ALTER TABLE `images`
    ADD CONSTRAINT `images_car_id_foreign` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`);
COMMIT;

