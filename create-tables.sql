SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS gifs (
  id int(11) NOT NULL AUTO_INCREMENT,
  catchPhrase varchar(255) NOT NULL,
  submissionDate datetime NOT NULL,
  submittedBy varchar(255) NOT NULL,
  publishDate datetime DEFAULT NULL,
  reportStatus tinyint(4) NOT NULL DEFAULT '0',
  gifStatus tinyint(4) NOT NULL DEFAULT '0',
  fileName varchar(32) NOT NULL,
  permalink varchar(255) NOT NULL,
  `source` varchar(255) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS users (
  id int(11) NOT NULL AUTO_INCREMENT,
  userName varchar(64) NOT NULL,
  email varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
