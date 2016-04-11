--
-- Table structure for table `pmt_attempts`
--
CREATE TABLE IF NOT EXISTS `pmt_attempts` (
  `attempt_id` int(11) NOT NULL AUTO_INCREMENT,
  `attempt_percent_mark` float NOT NULL,
  `attempt_time_start` varchar(255) NOT NULL,
  `attempt_time_end` varchar(255) NOT NULL,
  `attempt_complete` enum('Y','N') NOT NULL,
  `course_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  PRIMARY KEY (`attempt_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1186 ;
