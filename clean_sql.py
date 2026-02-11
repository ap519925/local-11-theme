import re
import gzip
import shutil

input_file = r"C:\Users\thean\Downloads\db-fresh-export.sql"
output_file = r"C:\Users\thean\Downloads\ibew-pantheon-clean.sql"

print(f"Reading {input_file}...")

with open(input_file, 'r', encoding='utf-8', errors='ignore') as f:
    content = f.read()

# 1. Replace utf8mb4_0900_ai_ci with utf8mb4_general_ci
content = content.replace("utf8mb4_0900_ai_ci", "utf8mb4_general_ci")

# 2. Replace utf8mb4_unicode_520_ci with utf8mb4_general_ci
content = content.replace("utf8mb4_unicode_520_ci", "utf8mb4_general_ci")

# 3. Remove NO_AUTO_VALUE_ON_ZERO mode if present
content = content.replace("NO_AUTO_VALUE_ON_ZERO", "")

# 4. Remove DEFINER clauses (e.g. DEFINER=`root`@`localhost`)
content = re.sub(r"DEFINER=`[^`]+`@`[^`]+`", "", content)

# 5. Remove GTID_PURGED (often causes issues on import)
content = re.sub(r"SET @@GLOBAL.GTID_PURGED=.*?;", "", content, flags=re.DOTALL)

# 6. Remove 'ALGORITHM=UNDEFINED' view definitions (sometimes breaks)
content = content.replace("ALGORITHM=UNDEFINED", "")

print("Writing cleaned SQL...")
with open(output_file, 'w', encoding='utf-8') as f:
    f.write(content)

print(f"Compressing to {output_file}.gz...")
with open(output_file, 'rb') as f_in:
    with gzip.open(output_file + '.gz', 'wb') as f_out:
        shutil.copyfileobj(f_in, f_out)

print("Done! Upload 'ibew-pantheon-clean.sql.gz' to Pantheon.")
