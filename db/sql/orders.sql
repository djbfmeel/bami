CREATE TABLE ORDERS(
   ID             INTEGER       PRIMARY KEY   AUTOINCREMENT   NOT NULL,
   AMOUNT         INT           NOT NULL,
   ORDER_DATE     VARCHAR (20)  NOT NULL,
   USER           INT
);
