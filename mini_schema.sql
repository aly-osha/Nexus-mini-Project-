-- SQL Schema for mini database

CREATE TABLE adminnex (
    user_name VARCHAR(20) NOT NULL,
    password  VARCHAR(15),
    aid       INT,
    PRIMARY KEY (user_name)
);

CREATE TABLE course (
    cid INT NOT NULL,
    course_name VARCHAR(30),
    filename VARCHAR(255),
    filepath VARCHAR(255),
    PRIMARY KEY (cid)
);

CREATE TABLE student_details (
    sid INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(30) NOT NULL,
    e_mail VARCHAR(50),
    cid INT,
    dob DATE,
    address TEXT,
    PRIMARY KEY (sid),
    FOREIGN KEY (cid) REFERENCES course(cid)
);

CREATE TABLE student_user (
    user_name VARCHAR(20) NOT NULL,
    password VARCHAR(8) NOT NULL,
    sid INT,
    verified VARCHAR(3),
    register DATE,
    PRIMARY KEY (user_name),
    FOREIGN KEY (sid) REFERENCES student_details(sid)
);

CREATE TABLE teacher_details (
    tid INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(30) NOT NULL,
    e_mail VARCHAR(50),
    cid INT,
    dob DATE,
    address TEXT,
    PRIMARY KEY (tid),
    FOREIGN KEY (cid) REFERENCES course(cid)
);

CREATE TABLE teacher_user (
    user_name VARCHAR(20) NOT NULL,
    password VARCHAR(8) NOT NULL,
    tid INT,
    verified VARCHAR(3),
    PRIMARY KEY (user_name),
    FOREIGN KEY (tid) REFERENCES teacher_details(tid)
);
