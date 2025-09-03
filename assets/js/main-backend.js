/* Backend JS */

/** 
 * Copy codes (copy to clipboard) 
*/
if(document.getElementById("copy-recovery-codes")){

    document.addEventListener("DOMContentLoaded", function() {
        const copyBtn = document.getElementById("copy-recovery-codes");
        const codesList = document.getElementById("admin-recovery-codes-list");

        copyBtn.addEventListener("click", function() {
            const codes = Array.from(codesList.querySelectorAll("li"))
                .map(li => li.textContent.trim())
                .join("\n");

            navigator.clipboard.writeText(codes).then(() => {
                copyBtn.textContent = "✅ Codes copied!";
                setTimeout(() => copyBtn.textContent = "📋 Copy all codes", 2000);
            });
        });
    });

}