USE `3dprinthub_db`;
CREATE TABLE IF NOT EXISTS product_images (
  product_id INT PRIMARY KEY,
  image_path VARCHAR(255) NOT NULL,
  CONSTRAINT fk_product_images_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
