USE skillbarter;
-- Demo accounts: every password is password123
INSERT INTO users (full_name,email,password_hash,role,bio,department,semester) VALUES
('Admin User','admin@skillbarter.test','$2y$10$grvsHy9qPAgsIuAWHYjLWu5DwPw2OX26ZEz7QRksongA91sbPkUk.','admin','Platform administrator.','Administration',NULL),
('Ayesha Khan','ayesha@skillbarter.test','$2y$10$grvsHy9qPAgsIuAWHYjLWu5DwPw2OX26ZEz7QRksongA91sbPkUk.','user','Loves teaching coding and maths. Looking to pick up design.','Computer Science',5),
('Bilal Ahmed','bilal@skillbarter.test','$2y$10$grvsHy9qPAgsIuAWHYjLWu5DwPw2OX26ZEz7QRksongA91sbPkUk.','user','Creative student who edits videos for fun.','Media Studies',3),
('Sara Malik','sara@skillbarter.test','$2y$10$grvsHy9qPAgsIuAWHYjLWu5DwPw2OX26ZEz7QRksongA91sbPkUk.','user','IELTS 8.0 scorer, happy to help with English.','English Literature',7),
('Hamza Ali','hamza@skillbarter.test','$2y$10$grvsHy9qPAgsIuAWHYjLWu5DwPw2OX26ZEz7QRksongA91sbPkUk.','user','Full-stack developer in the making.','Software Engineering',6),
('Fatima Noor','fatima@skillbarter.test','$2y$10$grvsHy9qPAgsIuAWHYjLWu5DwPw2OX26ZEz7QRksongA91sbPkUk.','user','Guitar and photography hobbyist.','Fine Arts',4),
('Usman Tariq','usman@skillbarter.test','$2y$10$grvsHy9qPAgsIuAWHYjLWu5DwPw2OX26ZEz7QRksongA91sbPkUk.','user','SAT math tutor and debate club member.','Business Administration',5),
('Zainab Raza','zainab@skillbarter.test','$2y$10$grvsHy9qPAgsIuAWHYjLWu5DwPw2OX26ZEz7QRksongA91sbPkUk.','user','UI/UX designer who wants to learn more code.','Design',6);
INSERT INTO skills (user_id,category_id,title,level,description) VALUES
(2,1,'Python basics','expert','Variables, loops, functions and small projects.'),
(2,5,'Discrete structures','expert','Logic, sets, relations and proofs for exams.'),
(3,2,'Graphic design in Canva','intermediate','Posters, social media posts and branding basics.'),
(3,9,'Video editing with CapCut','expert','Cuts, transitions, captions and export settings.'),
(4,3,'English speaking and IELTS','expert','Speaking practice and IELTS strategies.'),
(4,6,'Essay and report writing','intermediate','Structure, clarity and proofreading.'),
(5,1,'Web development with PHP','intermediate','Forms, sessions and MySQL for beginners.'),
(5,1,'Git and GitHub','expert','Branches, pull requests and teamwork.'),
(6,4,'Guitar for beginners','intermediate','Chords, strumming and your first songs.'),
(6,8,'Mobile photography','expert','Lighting, composition and editing on a phone.'),
(7,7,'Public speaking','intermediate','Confidence, structure and handling nerves.'),
(7,10,'Digital SAT math','expert','Strategies and practice for the math section.'),
(8,2,'UI design in Figma','expert','Wireframes, components and prototypes.'),
(8,1,'JavaScript fundamentals','intermediate','DOM, events and small interactive pages.');
INSERT INTO skill_wants (user_id,title) VALUES
(2,'Graphic design'),
(2,'Guitar'),
(3,'Python'),
(3,'English speaking'),
(4,'Video editing'),
(4,'Web development'),
(5,'UI design'),
(5,'Public speaking'),
(6,'Git'),
(6,'Video editing'),
(7,'Python'),
(7,'Photography'),
(8,'Public speaking'),
(8,'Python');
INSERT INTO swaps (requester_id,owner_id,requested_skill_id,offered_skill_id,message,status,requester_done,owner_done) VALUES
(3,2,1,3,'I can teach you Canva in return.','completed',1,1),
(2,6,9,2,'Can I learn guitar in exchange for discrete structures?','pending',0,0),
(5,8,13,7,'Figma for PHP, sounds fair?','accepted',1,0),
(4,3,4,5,'IELTS practice for CapCut lessons?','completed',1,1),
(7,2,1,11,'Public speaking for Python basics?','pending',0,0),
(8,5,7,13,'Figma lessons for PHP.','rejected',0,0),
(6,5,8,10,'Mobile photography for Git lessons.','accepted',0,0);
INSERT INTO messages (swap_id,sender_id,body) VALUES
(1,3,'Thanks for accepting! Free on Saturday?'),
(1,2,'Yes, 5 pm works for me.'),
(3,5,'Can we meet online?'),
(3,8,'Sure, Google Meet tonight.'),
(7,6,'Hi! When are you free for a session?'),
(4,4,'Great, let us start this weekend.');
INSERT INTO reviews (swap_id,reviewer_id,reviewee_id,rating,comment) VALUES
(1,3,2,5,'Clear and patient teacher.'),
(1,2,3,5,'Canva tips were super useful.'),
(4,4,3,5,'Fantastic editing lessons.'),
(4,3,4,4,'Helpful IELTS speaking drills.');
