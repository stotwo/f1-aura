
import random

# Drivers: IDs 1 to 22
drivers = list(range(1, 23))
races = 24
points = {1:25, 2:18, 3:15, 4:12, 5:10, 6:8, 7:6, 8:4, 9:2, 10:1}

sql = []

for race_id in range(1, races + 1):
    random.shuffle(drivers)
    # Give some preference to top drivers for realism if needed? Nah, random is okay for now to fix the "emptiness"
    
    # Store top 3 for courses table update
    top3 = drivers[:3]
    
    # Update courses table
    sql.append(f"UPDATE courses SET p1_pilote_id={top3[0]}, p2_pilote_id={top3[1]}, p3_pilote_id={top3[2]} WHERE id={race_id};")
    
    for pos, driver_id in enumerate(drivers, 1):
        pts = points.get(pos, 0)
        time_str = "1:30:00.000" if pos == 1 else "+10.000s"
        sql.append(f"INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES ({race_id}, {driver_id}, {pos}, '{time_str}', {pts});")

with open('append_results.sql', 'w') as f:
    f.write('\n'.join(sql))
