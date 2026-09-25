// ================================================================
// MongoDB Init Script — Smart Flood Monitoring System
// Dijalankan saat container MongoDB pertama kali dibuat
// ================================================================

db = db.getSiblingDB('smart_flood_monitoring');

// Buat user untuk aplikasi
db.createUser({
    user: 'smart_flood_user',
    pwd: process.env.MONGO_INITDB_ROOT_PASSWORD,
    roles: [
        {
            role: 'readWrite',
            db: 'smart_flood_monitoring'
        }
    ]
});

// Buat collection sensor_data dengan schema validation
db.createCollection('sensor_data', {
    validator: {
        $jsonSchema: {
            bsonType: 'object',
            required: ['suhu', 'kelembaban', 'curah_hujan', 'level_air', 'created_at'],
            properties: {
                suhu: {
                    bsonType: 'number',
                    description: 'Suhu dalam Celsius'
                },
                kelembaban: {
                    bsonType: 'number',
                    description: 'Kelembaban dalam %'
                },
                curah_hujan: {
                    bsonType: 'number',
                    description: 'Curah hujan dalam mm'
                },
                level_air: {
                    bsonType: 'number',
                    description: 'Level air dalam cm'
                },
                created_at: {
                    bsonType: 'date',
                    description: 'Timestamp data'
                }
            }
        }
    },
    validationAction: 'warn'
});

// Buat collection ai_detection
db.createCollection('ai_detection', {
    validator: {
        $jsonSchema: {
            bsonType: 'object',
            required: ['location', 'status', 'timestamp'],
            properties: {
                location: {
                    bsonType: 'string',
                    description: 'Lokasi CCTV'
                },
                status: {
                    bsonType: 'string',
                    enum: ['BANJIR', 'TIDAK BANJIR'],
                    description: 'Status deteksi banjir'
                },
                confidence: {
                    bsonType: 'number',
                    description: 'Confidence score 0-1'
                },
                timestamp: {
                    bsonType: 'date',
                    description: 'Timestamp deteksi'
                }
            }
        }
    },
    validationAction: 'warn'
});

// Buat indexes untuk performa query
db.sensor_data.createIndex({ created_at: -1 });
db.sensor_data.createIndex({ created_at: 1 }, { expireAfterSeconds: 2592000 }); // TTL 30 hari

db.ai_detection.createIndex({ timestamp: -1 });
db.ai_detection.createIndex({ location: 1, timestamp: -1 });

print('✅ MongoDB Smart Flood Monitoring initialized successfully');
print('   Collections: sensor_data, ai_detection');
print('   Indexes created');
