import random

# Generate a random IP address
ip = '.'.join(str(random.randint(0, 255)) for _ in range(4))
print(ip)
