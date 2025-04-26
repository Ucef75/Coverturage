<style>
    footer {
     background-color: var(--dark);
     color: white;
     text-align: center;
     padding: 2rem;
 }
 
 .footer-content {
     display: flex;
     flex-direction: column;
     align-items: center;
     justify-content: center;
 }
 
 .credits {
     margin-top: 1rem;
     font-size: 0.9rem;
     color: var(--text-light);
 }
 
 .contact-info {
     display: flex;
     gap: 1rem;
     margin-top: 0.5rem;
 }
 
 .contact-info a {
     color: var(--secondary);
     text-decoration: none;
     transition: color 0.3s;
 }
 
 .contact-info a:hover {
     color: white;
     text-decoration: underline;
 }
 
</style>
<footer>
     <div class="footer-content">
         <p>&copy; 2025 ForsaDrive | <?= t('all_rights', 'All Rights Reserved') ?></p>
         <div class="credits">
             <p><?= t('created_by', 'Created by') ?> Aziz BEN SLIMEN & Youssef BEN ABID</p>
             <div class="contact-info">
                 <a href="tel:+21626295416"><i class="fas fa-phone"></i> 26295416 (+216)</a>
                 <a href="tel:+21629131170"><i class="fas fa-phone"></i> 29131170 (+216)</a>
             </div>
         </div>
     </div>
 </footer>