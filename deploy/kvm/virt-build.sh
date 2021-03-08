vm="centos-vm14"
os="centos-8.2"
ram="1536"
disk="32G"
vcpu="1"
key=~/.ssh/id_rsa.pub
pwd="Encrypted_PASSWORD_HERE"
bridge="virbr0"
ostype="generic"
osvariant="rhel8.2"

virt-builder \
  "${os}" \
  --hostname "${vm}" \
  --run ./customize.sh \
  --network \
  --format qcow2 -o /var/lib/libvirt/images/${vm}-disk01.qcow2 \
  --ssh-inject "root:file:${key}"



